<?php

namespace Modules\SmsReseller\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\Accounting\App\Models\Category;
use Modules\Accounting\App\Models\FundAccount;

class SmsResellerSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'api_key',
        'base_url',
        'default_income_category_id',
        'default_expense_category_id',
        'default_tax_category_id',
        'default_fund_account_id',
        'default_wallet_fund_account_id',
        'operator_selling_prices',
    ];

    protected $casts = [
        'operator_selling_prices' => 'array',
    ];

    public function defaultIncomeCategory()
    {
        return $this->belongsTo(Category::class, 'default_income_category_id');
    }

    public function defaultExpenseCategory()
    {
        return $this->belongsTo(Category::class, 'default_expense_category_id');
    }

    public function defaultTaxCategory()
    {
        return $this->belongsTo(Category::class, 'default_tax_category_id');
    }

    public function defaultFundAccount()
    {
        return $this->belongsTo(FundAccount::class, 'default_fund_account_id');
    }

    public function defaultWalletFundAccount()
    {
        return $this->belongsTo(FundAccount::class, 'default_wallet_fund_account_id');
    }
}
