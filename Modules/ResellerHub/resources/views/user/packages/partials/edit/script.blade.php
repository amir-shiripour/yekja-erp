<script>
    window.packageEditConfig = {
        allModules: @json($availableModules),
        catalog: @json($catalog),
        currencyLabel: '{{ $currencyLabel }}',
        oldModules: @json(old('modules', $package->modules ?? [])),
        oldFeatures: @json(old('features', $package->features ?? [])),
        oldPricing: {
            price_type: '{{ old('price_type', $package->price_type ?? $defaultPriceType) }}',
            monthly_price: {{ (int)old('monthly_price', $package->monthly_price ?? 0) }},
            quarterly_price: {{ (int)old('quarterly_price', $package->quarterly_price ?? 0) }},
            semi_annual_price: {{ (int)old('semi_annual_price', $package->semi_annual_price ?? 0) }},
            yearly_price: {{ (int)old('yearly_price', $package->yearly_price ?? 0) }},
            one_time_price: {{ (int)old('one_time_price', $package->one_time_price ?? 0) }},
            setup_fee: {{ (int)old('setup_fee', $package->setup_fee ?? 0) }},
            discount_type: '{{ old('discount_type', $package->discount_type ?? $defaultDiscountType) }}',
            discount_value: {{ (float)old('discount_value', $package->discount_value ?? 0) }},
            discount_starts_at: '{{ old('discount_starts_at', $package->discount_starts_at_jalali ?? '') }}',
            discount_expires_at: '{{ old('discount_expires_at', $package->discount_expires_at_jalali ?? '') }}'
        },
        oldActivePeriods: @json(old('active_periods', $defaultActivePeriods)),
        oldPeriodPrices: @json(old('period_prices', $package->period_prices ?? [])),
        oldPeriodDiscounts: @json($formattedPeriodDiscounts),
        oldLimits: @json(old('limits', $package->limits ?? []))
    };

    function packageEditManager() {
        const config = window.packageEditConfig;

        const rawActive = Array.isArray(config.oldActivePeriods) && config.oldActivePeriods.length
            ? config.oldActivePeriods
            : [];

        const standardPresetMonths = (window.resellerDurationOptions || []).map(o => o.months);

        const initialMonths = [];
        rawActive.forEach(item => {
            const m = (window.resolvePeriodMonth ? window.resolvePeriodMonth(item) : null) || (parseInt(item, 10) || null);
            if (m && m >= 1 && m <= 1200 && !initialMonths.includes(m)) {
                initialMonths.push(m);
            }
        });

        // Also check keys in oldPeriodPrices
        if (config.oldPeriodPrices && typeof config.oldPeriodPrices === 'object') {
            Object.keys(config.oldPeriodPrices).forEach(k => {
                const m = (window.resolvePeriodMonth ? window.resolvePeriodMonth(k) : null) || (parseInt(k, 10) || null);
                if (m && m >= 1 && m <= 1200 && !initialMonths.includes(m)) {
                    initialMonths.push(m);
                }
            });
        }
        initialMonths.sort((a, b) => a - b);

        const initialPrices = (config.oldPeriodPrices && typeof config.oldPeriodPrices === 'object')
            ? {...config.oldPeriodPrices}
            : {};

        if (!initialPrices['1'] && config.oldPricing.monthly_price) initialPrices['1'] = config.oldPricing.monthly_price;
        if (!initialPrices['3'] && config.oldPricing.quarterly_price) initialPrices['3'] = config.oldPricing.quarterly_price;
        if (!initialPrices['6'] && config.oldPricing.semi_annual_price) initialPrices['6'] = config.oldPricing.semi_annual_price;
        if (!initialPrices['12'] && config.oldPricing.yearly_price) initialPrices['12'] = config.oldPricing.yearly_price;

        return {
            allModules: config.allModules || [],
            catalog: config.catalog || [],
            selectedModules: Array.isArray(config.oldModules) ? [...config.oldModules] : [],
            selectedFeatures: Array.isArray(config.oldFeatures) ? [...config.oldFeatures] : [],
            moduleLimits: config.oldLimits && typeof config.oldLimits === 'object' ? {...config.oldLimits} : {},
            expandedModules: [],

            selectedColor: '{{ old('color', $package->color_hex ?? $defaultColor) }}',
              '{{ old('icon', $package->icon ?? $defaultIcon) }}
                   pak {{ old('name', $package->name) }}
        ',
  tive: {{ old('is_active', $package->isActive() ? 'true' : 'false') ? 'true' : 'false' }}
        ,
  e,



          standardPresetMonths: standa dP ese
                   all
    P
        riodOptions: window re el
             []

             alMonth .map(m =>
              id: Mat .random(),
                    months: Nu
             !standa
    r
        P
            Number(m))
                })),

                pricing: {
                    p
                Pricing.price
        _
            ype || '{{ $defaultPriceType }}
            ubscription |  ne_time | free
                    perio _p ice


            monthly_price: parseInt(config.oldPricing. on hly
                             quar
        t
            rly_price: parseInt(config.oldPricing.quarte ly pri
                         sem
        i
            annual_price: parseInt(config.oldPricin .s mi_


               yearly_price: parseInt(config.oldPrici g. ear


                one_time_price: parseInt(config. ld ric  0),


                      setup_fee: parseIn
                etup_fee || 0)
        ,
                                discount_type: '{{ $defaultDiscountType }}
            ount (Toman)
               discount_value: parseFl at con nt_value || 0),


                           discount_starts_at: co fi .ol rts_at || '',


                            discount_e pi es_at: config.oldPricing.discoun _ex ires_at |   '
                ,
                   period
            _ i co
        P
    e
        ype
        f config.oldPer
        o
            t'  ? {...config.oldPeriodD sco nts} : {}


                        get isFreePackage() {
                    if
            t

                       r tur  ! parseI t(thi
            ice) > 0);
                    }
                                    if (this.periodRows.length === 0  r tu n tru


        eriodRows.some(r w => (parseInt(this.pricing. er od_ ) || 0) > 0);


                      },

          lors:
        i
            olors || [],


                in ow.industryIcons || [],


                                  this.$nextTick(() => {
                        ow.jalali atepicker) {
 w.jalali atepic

                :


                       '

                                    );
                        }
                 ;



              ...(window.resel e UiHe
        s
    e
        {}),


        e
                          his.selectedColor = val;
                },


            l() {
                     o st found = th
        .
    f
         this.selectedIcon);


            rn found ?  oun .la el : 'پکی  عم
             },

               for a PriceNumber(num) {

        m

        rn '';


            tAmoun  ? this.formatAmount n m) : num;
                }

                 ar eCl


        return this.parsePric
        I
            eInpu (val) : (parseInt(val, 10) ||  );
                                }

            Module llowed(slug)

        s
    .
        => m.slug === slug);


            rn Boo ean(found && found.is_allowed);




        uleSelected(slug)


            eturn thi . electedModules.includes(slug);


                       to g
                            const idx = this.sel cte
            )

                -1) {
                        th
                (i x, 1);
                    } else {

                    .push(slug);

                u



        this.selectAllModu
        e
                               }
                                    }
                },

                sel
                             this.selectedMod le
                m => m.is_allowed).map(m => m.slug)
             th
        s
    .


            llModuleFeatures(slu )


             },


        t
    A
                 this.selectedModul
        s
                  thi . electedFeatures = [];


               oggl M dul A
                            const idx = this.exp nde
            )

                -1) {
                        th
            l


        se {


            ules.p sh(slug);
                    }




        uleExpanded(slug) {


             this expan e Modules.includes(sl g)


            etModu eFeatu es slug) {
                    cons   oun
        f
    i
         slug);
    re
        u
            atures  ? found.features : [];
                 ,

                get
        u
    r
                     return t
        i
            lug).f lter(f => f.type === 'feature');
                },


        Q
    u
                       return this.g
        t
            filter f => f.type === 'quota');
                }



        ount(slug) {
   ret
        r
            anFea ures(slu ) length;
                },

                 etSelec
            t(slug  {
                    const f at eys = this.getModuleBooleanFe
        f

                     return this.se
        e
             => f atKeys.in l des(k)).length;
                },


            maryL bel(slug)
                    const boolTotal
            ea Features(s ug) l ng h;

                is.get oduleQuotas(slug).len

            0 &&  uotaT t l =
                       re u n  ب
                               }
                    const parts = [];
                     f (boolTotal > 0) {

            a
            od leFeaturesC u t( l
                m(boolTotal) + ' قابلیت فعال');


            {

        f
    a
        ' سهمیه عددی');


                     return parts.join(' | ');




        cted(featKey) {


            ected eat r s.includes(featKey);
                },

            gl Feat r (fe t
                        const idx = this.selected eat
            ;
                1) {
                        this.se
                ,  );
                    } else {

                    Features.push(featKey);

                .
            d


           this.selectedModules.push(
        l
                    }
                    }
                },

                s
            s(slug) {

                hi .getModuleBooleanFeatures(slug).map(
                      featKeys.forEach(k => {

                t
            inc


           this.selectedFeatures.push(k
        ;
                }
                                    });
                },

                des
            s(slug) {
                    const featKeys = this.ge Mo uleBooleanFeatures(slug
        ;


          this.selectedFeature  = this.s
        l
            k => !featKeys.incl d s(k));
                },


        c
    i
        Value) {
        this.prici
        g
            leanNu ber(rawValue);
                },

                                isMonthTakenE sew ere(month, curr


        his.periodRows.some(

             && Nu ber(r.months) === Numb r( onth));
                },


        t
    i
                      return thi . eriod
        o
            .m nths) === Number(month) ;

               ad PeriodRow( u tomMonth = null) {
                    if (
            h >  30) retu n
                    const usedMo t s
            (r => Number(r.mo t
                 le  nex M n h    us omM nth
                                    if (!customM n
                         (let i = 1  i

                    i

            =
                          bre k
                            }
                        }

                       cons   a eM nthly = parseInt(this.getPeriodP i
                thl _price || 0 ;
                    if
                s. etPeriodPr ce n xt onth)) {

                     = baseMo t ly * nextMonth;

                >  6 &  n xtMonth <  2) {

                    Price = M t .round(calcPrice * 0 9 );

                s
                                            ca cPrice = Ma
            0
                    }
                        this.setPeriodPrice(nextMonth,
                       }

        m = !this.stan a dPresetMonths.                  t                   ath
                          months:


        Custom: isCustom


                            t is.sortPeriodRows();
                },


        v
    e

 this
        p
            odRows.fil e (r => r.id !== rowI
            ,

                handleRowMonthChange(row) {

            er(row.months);

        .
    i
        andardPresetMonths.in
        l
                             this.sortPer
                  },

                toggleCustomMode(row) {
                    row.isCus o
                              i  (!row.isCustom && !this.all er odOptions.some(o => o.months === row. onths)) {
                co st neares
                    o => !this i MonthTakenElsew
                                        if (nearest

            a


            this.handleRowMonth hang
        (
                     }
                    }

                          et o C st
                                let
            mber( al);

             = 1;

                00;

                    const oldMonth = row.m
                   f (oldMon h ! =  ) {

                    riodPrice(oldMonth);

                >
            P

            (m, oldPrice ;

                                }



                            ro
        .
                            this.sortPer o Rows();
                },

                getPeri



         return this.get
        u
            tDurationLabel(months) : (m nt s + ' ما ه );



        odRows() {


            ws.sort((a, b)  > a.m nt s   b.months ;

                             },

                select ommon
            Peri


        odRows = [1, 3, 6,
        1
            h.ran om()   ont
            e}) ;
                },


                ds() {

                                    co st rows =  ];


            m
                              rows.
        n
    d
        sCustom: false});

                                t
        r
    o
          },

      cl
        a
                           this.periodRows = [];
                },

                reso v Period e (months) {


        solvePeriodKey ? windo
        .
            hs) :  ' eriod_' + month
                      },

                getPeriodPr ce mo


        String(months);


            ng.pe iod p ices[months] || 0;

                setPeriodPrice(months, rawVal) {

            hi .parseCleanNumb r(r wV l);
                    th s pric
            in (months)] = num
                    if (Number(mon h ) ==
            th y_price = num;
                    if (Number(months) = = 3)
            ly price = num;
                    if (Number(month ) ===
        e
    m
        um;

        f
            12) t is.pricing. e rly_price = num;
                }

                autoCalc ac age
                       const  aseMont
            etPeriodPrice(1) || this. ri i



                    h(r => {
                        con
                                i
                               this.setPer odPrice(1,  a eMo
                       }  ls   f (
                          this.setPeriodPr ce(m, baseMonthly * m)

                < 12)

                    his.setPeriodPrice(m,  ath.round(baseMonthly        .95));



        e
    t
        h.round(baseMonthly * m * 0.8
        )
                  }
                    });

                      al Di counte Pr
                           const price = parseInt(rawPrice) ||  ;

            pr ce <  0  r turn 0

            val =  arseFloat(t is.pricing.disco n _value


        = 0) return price;


            urn Ma h.max(0, Math.round(price - val));



        () {

        e
            .prici g discount_value)
                   },

                getPeriodDiscoun (
                     months = String(months);

                    riod_discou ts[mon
                       this.pricing pe

                                        dis
                                   disco nt

            c
                                       discount_expires_at: ''

                    }
                    if (this.pricing.perio _ iscounts[months].has_custom === undefined) {
                        th s pr
            s
             (pars Float(this.pricing.period_discounts[mo
        l
    u
                        }

        e
            iod_d sco n s[months];
                },

            eriod i c untValue(months, rawVal) {

             num = this.pars C eanN


        nst d = this.getPeriodDiscount(mon
        h
               d. i c unt_value = num;

             g tEffectivePeri d
                              const d = this.getPeriodDi co nt
                     i
                                c nst v l  ) || 0

                            has_d

            d
            ount',
                                        is_
                            };
                    }

            bal pa k
                        const gVal = pa count_ alue)   ret rn {
     iscount: g al >



            type: 'amount',


            alse
                    };
                }
            lc iscounted er od rice(m nt
                  ons   awPrice = this.getPeriodPrice(months);

             ( awPrice <= 0) retu n  ;
                     onst eff
            riodDi count(month );
                    if (!eff.ha
        .
    v
    t
        u
    c</script>
