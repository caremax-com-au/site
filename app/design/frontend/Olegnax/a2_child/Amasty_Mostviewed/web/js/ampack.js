define([
    'jquery',
    'underscore',
    'matchMedia',
    'Magento_Catalog/js/price-utils',
    'Magento_Catalog/js/price-box'
], function ($, _, mediaCheck, utils) {
    $.widget('mage.amPack', {
        options: {},
        excluded: [],
        mediaBreakpoint: '(min-width: 768px)',
        selectors: {
            'discount': '[data-amrelated-js="bundle-price-discount"]',
            'finalPrice': '[data-amrelated-js="bundle-final-price"]',
            'checkbox': '[data-amrelated-js="checkbox"]',
            'packContainer': '[data-amrelated-js="pack-container"]',
            'packWrapper': '[data-amrelated-js="pack-wrapper"]',
            'packItem': '[data-amrelated-js="pack-item"]',
            'packTitle': '[data-amrelated-js="pack-title"]',
            'selectedBackground': '[data-amrelated-js="selected-background"]',
            'mainPackItem': '[data-item-role="main"]'
        },
        classes: {
            discountApplied: '-discount-applied',
            collapsed: '-collapsed',
            relatedLink: 'amrelated-link',
        },

        _create: function () {
            var self = this;

            $(this.element).find(this.selectors.checkbox).change(function () {
                var id_selector = $(this).attr('id');
                //console.log(id_selector);
                $('.qty-'+id_selector).prop( "disabled", false );
                self.changeEvent($(this));
            });

            this.observeClickOnMobile();
        },

        observeClickOnMobile: function () {
            var self = this;
            
            mediaCheck({
                
                media: self.mediaBreakpoint,
                entry: function () {
                    self.toggleCollapsingListeners(false);
                },
                exit: function () {
                    self.toggleCollapsingListeners(true);
                }
                
            });
        },

        toggleCollapsingListeners: function (isEnabled) {
            var self = this,
                packItem = $(this.element).find(this.selectors.packItem),
                packTitle = $(this.element).find(this.selectors.packTitle),
                target,
                checkbox;

            if (isEnabled) {
                packItem.on('click.amPack', function (event) {
                    target = $(event.target);

                    if (!target.hasClass(self.classes.relatedLink)
                        && !target.parents().hasClass(self.classes.relatedLink)
                    ) {
                        checkbox = target.parents(self.selectors.packItem).find(self.selectors.checkbox);

                        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
                    }
                });

                packTitle.on('click.amPack', function (event) {
                    self.toggleItems(event);
                });
            } else {
                packItem.off('click.amPack');
                packTitle.off('click.amPack');
                self.toggleItems(false);
            }
        },

        toggleItems: function (event) {
            var packContainer;

            if (event) {
                packContainer = $(event.target).parents(this.selectors.packWrapper);

                packContainer.find(this.selectors.packTitle).toggleClass(this.classes.collapsed);
                packContainer.find(this.selectors.packItem).toggleClass(this.classes.collapsed);
            } else {
                $(this.element).find('.' + this.classes.collapsed).removeClass(this.classes.collapsed);
            }
        },

        changeEvent: function (checkbox) {
            //console.log('clicking');
          
            var id = checkbox.closest(this.selectors.packItem).attr('data-product-id'),
                isChecked = checkbox.prop('checked'),
                packItem = checkbox.parents(this.selectors.packItem),
                isLastItem = packItem.is('.amrelated-pack-item:last-child'),
                packContainer = checkbox.parents(this.selectors.packContainer),
                itemsCount = packContainer.find(this.selectors.checkbox).length,
                packBackground = packContainer.find(this.selectors.selectedBackground),
                selectedItems = packContainer.find(this.selectors.checkbox + ':checked'),
                selectedItemsCount = selectedItems.length;

            if (isChecked) {
                packItem.addClass('-selected');
                this.excluded = this.excluded.filter(function (item) {
                    return item !== id
                });
            } else {
                packItem.removeClass('-selected');
                this.excluded.push(id);
            }

            if (packContainer.length && itemsCount > 1) {
                var rtlCondition = (isChecked && selectedItemsCount === 1) || (!isChecked && selectedItemsCount === 0);
                packBackground.toggleClass('rtl', rtlCondition ? isLastItem : !isLastItem);
            }

            if (selectedItemsCount === itemsCount) {
                packContainer.addClass('-selected');
                packBackground.width("100%");
            } else if (selectedItemsCount === 0) {
                packContainer.removeClass('-selected');
                packBackground.width(0);
            } else {
                packContainer.removeClass('-selected');
                packBackground.width(selectedItems.parents(this.selectors.packItem).outerWidth())
            }

            this.reRenderPrice();
        },

        reRenderPrice: function () {
            var selected_id = $(this.element).attr('id');
            var self = this,
                saveAmount = 0,
                isAllUnchecked = false,
                parentPrice = +this.options.parent_info.price,
                oldPrice = parentPrice,
                newPrice = 0,
                $element = $(this.element),
                priceFormat = this.options.priceFormat;
               // var selected_arr = [];
            $.each(this.options.products, function (index, priceInfo) {

                if (self.excluded.indexOf(index) === -1) {
                    {
                        
                        var selected_only = selected_id.replace("pack", "checkbox"+index);   
                        
                        //amrelated_products[1379]
                        //console.log('before loop'+selected_only);   
                        
                        

                       
                            //console.log('Not');
                            if($('#'+selected_only).is(':checked'))
                            {
                                
                 
                            //console.log(' pp '+index);
                            //console.log('loop'+selected_only);   
                            isAllUnchecked = true;
                            var qty = $('#'+selected_only).attr('value');
                            oldPrice += priceInfo.price * qty;
                            var new_priceInfo = {"price":priceInfo.price,"qty":qty};
                            newPrice += self.applyDiscount(new_priceInfo , index);
                            console.log('checked'+newPrice);
                            //console.log('oldmmm '+priceInfo.price * priceInfo.qty);
                            //console.log('New'+newPrice);
                              
                            }   
                        
                        
                        //selected_arr.push(selected_only); 
                    }
                    
                }
            });
            
            if (isAllUnchecked) {
                newPrice += this.options.apply_for_parent ? this.applyDiscount(this.options.parent_info) : parentPrice;
               
            } else {
                console.log('new');
                newPrice += this.options.apply_for_parent ? this.applyDiscount(this.options.parent_info) : parentPrice;
            }

            this.toggleMainItemDiscount(!isAllUnchecked);

            saveAmount = oldPrice - newPrice;
            //console.log('old price'+oldPrice);
            //console.log('New Price'+newPrice);
            $element.find(this.selectors.discount).html(utils.formatPrice(saveAmount, priceFormat));
            $element.find(this.selectors.finalPrice).html(utils.formatPrice(newPrice, priceFormat));
        },

        toggleMainItemDiscount: function (visible) {
            var mainPackItem = $(this.selectors.mainPackItem);

            if (visible) {
                mainPackItem.addClass(this.classes.discountApplied);
            } else {
                mainPackItem.removeClass(this.classes.discountApplied);
            }
        },

        applyDiscount: function (priceInfo , product_id ) {
            console.log('price '+priceInfo.price);
            console.log('qty '+priceInfo.qty);
            var price = priceInfo.price;
            var discount_percentage = 0;
            var fix_discount = 0;
            if (this.options.discount_type == 0) {
                //fix_discount = this.options.discount_amount;
                fix_discount = this.options.products[product_id].discount_amount;
                price = (price > fix_discount)
                    ? (price - fix_discount) * priceInfo.qty
                    : 0;
            } else {
                //discount_percentage = this.options.discount_amount 
                discount_percentage = this.options.products[product_id].discount_amount;
                price = price - parseFloat(
                    (Math.round((price * 100) * discount_percentage / 100) / 100).toFixed(2)
                );
                price *= priceInfo.qty;
            }
          
            //console.log(' percentage '+ discount_percentage );
            //console.log(' options ');
            //console.log(this.options);


            return price;
        }
    });

    return $.mage.amPack;
});
