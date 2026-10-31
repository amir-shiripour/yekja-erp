# مستندات فنی: بانک اطلاعاتی محلی مناسبت‌ها و تعطیلات (persian_holidays.json)

این مستند نحوه استخراج، ساختار داده و راهنمای به‌روزرسانی فایل `database/data/persian_holidays.json` را برای استفاده در آینده توضیح می‌دهد.

---

## ۱. منبع اصلی داده‌ها (Data Origin)

- **پروژه مرجع:** [samanzamani/PersianHoliday (GitHub)](https://github.com/samanzamani/PersianHoliday)
- **منبع اولیه داده‌ها:** داده‌های استخراج‌شده رسمی از وب‌سایت **time.ir**
- **بازه زمانی پوشش‌داده‌شده:** سال ۱۳۷۰ تا ۱۴۲۰ شمسی (بیش از ۵۰ سال شامل ۴,۸۶۴ مناسبت دقیق).

---

## ۲. ساختار پایگاه داده مرجع (SQLite Schema)

داده‌های مرجع از فایل `persian_holiday.db` جدول `events` با مشخصات زیر خوانده شده‌اند:

| نام ستون | نوع داده | توضیح |
|---|---|---|
| `year` | INTEGER | سال شمسی (مثلاً ۱۴۰۵) |
| `month` | INTEGER | ماه شمسی (۱ تا ۱۲) |
| `day` | INTEGER | روز شمسی (۱ تا ۳۱) |
| `event` | TEXT | عنوان مناسبت استخراج‌شده از time.ir |
| `is_holiday` | INTEGER | آیا تعطیل رسمی است؟ (`1` = بله، `0` = خیر) |

---

## ۳. مراحل پاک‌سازی و تبدیل داده‌ها (Data Extraction Pipeline)

خروجی اولیه شامل برخی عناوین خلاصه و پیش‌وندهای تکراری بود که طبق مراحل زیر پالایش شدند:

1. **فیلتر رکوردهای خلاصه:** حذف رکوردهای مجموع که شامل متون طولانی (> ۱۲۰ کاراکتر) بودند.
2. **حذف پیش‌وندهای تکراری روز و ماه:**  
   عباراتی مانند `۷ اَمرداد` یا `۱ فروردین` که در ابتدای عناوین متون وجود داشتند با الگوی Regex زیر پاک‌سازی شدند:
   ```regex
   ^\d{1,2}\s+(?:فروردین|اردیبهشت|خرداد|تیر|مرداد|اَمرداد|شهریور|مهر|آبان|آذر|دی|بهمن|اسفند)\s*
   ```
3. **تبدیل به JSON کلید-مقدار بر اساس سال:**  
   داده‌ها بر اساس کلید سال (مانند `"1405"`) گروه‌بندی شده و در فایل زیر قرار گرفتند:
   `database/data/persian_holidays.json`

---

## ۴. نمونه فرمت خروجی فایل JSON

```json
{
  "1405": [
    {
      "jalali_date": "1405-05-07",
      "year": 1405,
      "month": 5,
      "day": 7,
      "title": "اَمرداد روز،جشن اَمردادگان",
      "is_holiday": false
    },
    {
      "jalali_date": "1405-05-13",
      "year": 1405,
      "month": 5,
      "day": 13,
      "title": "اربعین حسینی[ ۲۰ صفر ]",
      "is_holiday": true
    }
  ]
}
```

---

## ۵. نحوه عملکرد در پروژه لاراول

1. فایل `database/data/persian_holidays.json` درون مخزن پروژه نگه داشته می‌شود.
2. کلاس [`App\Services\JalaliHolidayService`](file:///e:/Project/Web/Anti-Gravity/laravel-crm/app/Services/JalaliHolidayService.php) هنگام اجرای متد `syncCurrentAndNextYears()` یا کلیک روی دکمه به‌روزرسانی در تنظیمات سیستم، داده‌های سال جاری و بعدی را از این فایل محلی خوانده و در جدول `holiday_events` دیتابیس MySQL سیستم درج/به‌روزرسانی می‌کند.
3. تمام استعلام‌های تقویم در پنل کاربران مستقیماً و ۱۰۰٪ آفلاین از جدول MySQL `holiday_events` خوانده می‌شوند.

---

## ۶. اسکریپت به‌روزرسانی برای سال‌های جدید (برای آینده)

در صورتی که در آینده خواستید داده‌ها را مجدداً از فایل `.db` خروجی بگیرید یا سال‌های جدیدتر اضافه کنید، می‌توانید اسکریپت پایتون زیر را اجرا نمایید:

```python
import sqlite3
import json
import re
import os

db_path = 'persian_holiday.db'
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

cursor.execute("SELECT year, month, day, is_holiday, event FROM events WHERE year >= 1400 AND year <= 1420 ORDER BY year ASC, month ASC, day ASC, id ASC;")
rows = cursor.fetchall()

holidays_by_year = {}
month_names = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'اَمرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند']
pattern = re.compile(rf'^\d{{1,2}}\s+(?:{"|".join(month_names)})\s*', re.UNICODE)

for y, m, d, is_h, ev in rows:
    if not ev or len(str(ev).strip()) > 120:
        continue
    clean_title = pattern.sub('', str(ev).strip()).strip()
    if not clean_title:
        continue
    date_fa = f"{y}-{m:02d}-{d:02d}"
    yr_str = str(y)
    if yr_str not in holidays_by_year:
        holidays_by_year[yr_str] = []
    holidays_by_year[yr_str].append({
        'jalali_date': date_fa,
        'year': y, 'month': m, 'day': d,
        'title': clean_title,
        'is_holiday': bool(is_h)
    })

with open('database/data/persian_holidays.json', 'w', encoding='utf-8') as f:
    json.dump(holidays_by_year, f, ensure_ascii=False, indent=2)
```
