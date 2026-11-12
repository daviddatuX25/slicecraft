import './bootstrap';

import Alpine from 'alpinejs';

// NOTE (change NOT in the worksheet instructions): Alpine powers the small
// preview and confirmation interactions added to the midterm Page 3 without
// converting the Blade application into a single-page app.
window.Alpine = Alpine;

window.cakeBuilder = (products, options, customers) => ({
    products,
    options,
    customers,
    selectedProductId: products[0]?.id ?? null,
    selectedCustomerId: customers[0]?.id ?? null,
    selectedSize: 'one-kg',
    selectedFilling: 'strawberry',
    selectedFrosting: 'buttercream',
    selectedToppings: [],
    selectedDecoration: 'minimal',
    message: '',
    isCondensed: false,
    scrollThreshold: 28,

    get selectedProduct() {
        return this.products.find((product) => product.id === this.selectedProductId) ?? this.products[0];
    },

    get selectedPreview() {
        return this.selectedProduct?.previews?.[this.previewKey] ?? null;
    },

    get previewKey() {
        const previews = this.selectedProduct?.previews ?? {};

        const hasTopping = (code) => this.selectedToppings.includes(code);

        const deluxeKeys = [
            [hasTopping('chocolate-drip') && hasTopping('macarons'), 'deluxe-drip-macarons'],
            [hasTopping('chocolate-drip') && hasTopping('fresh-fruit'), 'deluxe-drip-berries'],
            [hasTopping('fresh-fruit') && hasTopping('macarons'), 'deluxe-berries-macarons'],
            [hasTopping('coconut') && hasTopping('fresh-fruit'), 'deluxe-coconut-berries'],
            [this.selectedDecoration === 'celebration' && hasTopping('coconut'), 'deluxe-rosettes-coconut'],
        ];

        const deluxeKey = deluxeKeys.find(([isSelected, key]) => isSelected && previews[key]);

        if (deluxeKey) {
            return deluxeKey[1];
        }

        if (this.selectedSize === 'bento') {
            return 'bento';
        }

        const toppingKeys = {
            'fresh-fruit': ['fruit', 'berries', 'tropical-fruit'],
            coconut: ['coconut'],
            'chocolate-drip': ['drip', 'web-glaze'],
            macarons: ['macarons'],
        };

        for (const topping of this.selectedToppings) {
            const matchingKey = toppingKeys[topping]?.find((key) => previews[key]) ?? toppingKeys[topping]?.[0];

            if (matchingKey) {
                return matchingKey;
            }
        }

        const decorationKeys = {
            floral: ['floral', 'rosettes', 'web-glaze'],
            celebration: ['rosettes', 'web-glaze', 'floral'],
        };
        const decorationKey = decorationKeys[this.selectedDecoration]?.find((key) => previews[key])
            ?? decorationKeys[this.selectedDecoration]?.[0];

        return decorationKey ?? 'minimal';
    },

    get previewImage() {
        return this.selectedPreview?.image_url ?? this.selectedProduct?.image_url;
    },

    get selectedSizeOption() {
        return this.options.sizes.find((option) => option.code === this.selectedSize);
    },

    get selectedFillingOption() {
        return this.options.fillings.find((option) => option.code === this.selectedFilling);
    },

    get selectedFrostingOption() {
        return this.options.frostings.find((option) => option.code === this.selectedFrosting);
    },

    get selectedDecorationOption() {
        return this.options.decorations.find((option) => option.code === this.selectedDecoration);
    },

    get selectedToppingOptions() {
        return this.options.toppings.filter((option) => this.selectedToppings.includes(option.code));
    },

    get optionTotalCents() {
        const size = this.selectedSizeOption?.price_adjustment_cents ?? 0;
        const filling = this.selectedFillingOption?.price_adjustment_cents ?? 0;
        const frosting = this.selectedFrostingOption?.price_adjustment_cents ?? 0;
        const decoration = this.selectedDecorationOption?.price_adjustment_cents ?? 0;
        const toppings = this.selectedToppingOptions.reduce((total, option) => total + option.price_adjustment_cents, 0);

        return size + filling + frosting + decoration + toppings;
    },

    get estimatedPriceCents() {
        return (this.selectedProduct?.base_price_cents ?? 0) + this.optionTotalCents;
    },

    formatPrice(cents) {
        return new Intl.NumberFormat('en-PH', {
            style: 'currency',
            currency: 'PHP',
        }).format(cents / 100);
    },

    selectProduct(productId) {
        this.selectedProductId = productId;
        this.selectedToppings = [];
        this.selectedSize = 'one-kg';
    },

    updateHeader(event) {
        this.isCondensed = event.target.scrollTop > this.scrollThreshold;
    },

    toggleTopping(code) {
        if (this.selectedToppings.includes(code)) {
            this.selectedToppings = this.selectedToppings.filter((topping) => topping !== code);
            return;
        }

        this.selectedToppings = [...this.selectedToppings, code];
    },
});

Alpine.start();
