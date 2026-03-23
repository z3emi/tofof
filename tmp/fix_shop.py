# Script to add missing keys to shop.php translation files and update shop.blade.php

import re

def update_file(path, old, new):
    with open(path, 'r', encoding='utf-8') as f:
        c = f.read()
    count = c.count(old)
    c = c.replace(old, new)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(c)
    return count

# ---- 1. Add missing keys to lang/ar/shop.php ----
ar_shop = r'c:\xampp\htdocs\tofof\lang\ar\shop.php'
update_file(ar_shop,
    "    'no_results'             => '\u0644\u0627 \u062a\u0648\u062c\u062f \u0646\u062a\u0627\u0626\u062c.',",
    "    'no_results'             => '\u0644\u0627 \u062a\u0648\u062c\u062f \u0646\u062a\u0627\u0626\u062c.',\n    'in_section'             => '\u0641\u064a \u0642\u0633\u0645',\n    'showing_range'          => '\u0639\u0631\u0636 :from\u2013:to \u0645\u0646 :total \u0645\u0646\u062a\u062c',\n    'no_results_for_term'    => '\u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0646\u062a\u062c\u0627\u062a \u0645\u0637\u0627\u0628\u0642\u0629 \u0644\u0639\u0628\u0627\u0631\u0629 \u0627\u0644\u0628\u062d\u062b:',\n    'no_results_currently'   => '\u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0646\u062a\u062c\u0627\u062a \u0645\u0637\u0627\u0628\u0642\u0629 \u062d\u0627\u0644\u064a\u0627\u064b.',")
print("ar/shop.php updated")

# ---- 2. Add missing keys to lang/en/shop.php ----
en_shop = r'c:\xampp\htdocs\tofof\lang\en\shop.php'
update_file(en_shop,
    "    'no_results'             => 'No results.',",
    "    'no_results'             => 'No results.',\n    'in_section'             => 'in section',\n    'showing_range'          => 'Showing :from\u2013:to of :total products',\n    'no_results_for_term'    => 'No products match the search term:',\n    'no_results_currently'   => 'No matching products currently.',")
print("en/shop.php updated")

# ---- 3. Update shop.blade.php ----
shop = r'c:\xampp\htdocs\tofof\resources\views\frontend\shop.blade.php'
with open(shop, 'r', encoding='utf-8') as f:
    c = f.read()

original = c

# Title
c = c.replace("@section('title', $pageTitle ?? '\u0627\u0644\u0645\u062a\u062c\u0631')",
              "@section('title', $pageTitle ?? __('shop.title'))")

# Search hero heading pieces
c = c.replace("                \u0646\u062a\u0627\u0626\u062c \u0627\u0644\u0628\u062d\u062b \u0639\u0646: <span>",
              "                {{ __('shop.search_results_for') }} <span>")
c = c.replace("                    \u0641\u064a \u0642\u0633\u0645 \"{{ $currentCategory->name_ar }}\"",
              "                    {{ __('shop.in_section') }} \"{{ $currentCategory->name_ar }}\"")

# Category hero subtitle
c = c.replace("<p class=\"category-hero-subtitle\">\u0623\u0646\u062a \u062a\u062a\u0635\u0641\u062d \u0642\u0633\u0645</p>",
              "<p class=\"category-hero-subtitle\">{{ __('shop.browsing_section') }}</p>")

# Showing range
c = c.replace("            \u0639\u0631\u0636 {{ $from }}\u2013{{ $to }} \u0645\u0646 {{ $products->total() }} \u0645\u0646\u062a\u062c",
              "            {{ __('shop.showing_range', ['from'=>$from, 'to'=>$to, 'total'=>$products->total()]) }}")

# Sort label
c = c.replace("<label class=\"toolbar-label\">\u0627\u0644\u062a\u0631\u062a\u064a\u0628:</label>",
              "<label class=\"toolbar-label\">{{ __('shop.sort_by') }}</label>")

# Sort options
c = c.replace("'selected' : '' }}>\u0627\u0644\u0623\u062d\u062f\u062b</option>",
              "'selected' : '' }}>{{ __('shop.newest') }}</option>")
c = c.replace("'selected' : '' }}>\u0627\u0644\u0633\u0639\u0631: \u0645\u0646 \u0627\u0644\u0623\u0642\u0644 \u0644\u0644\u0623\u0639\u0644\u0649</option>",
              "'selected' : '' }}>{{ __('shop.price_low_to_high') }}</option>")
c = c.replace("'selected' : '' }}>\u0627\u0644\u0633\u0639\u0631: \u0645\u0646 \u0627\u0644\u0623\u0639\u0644\u0649 \u0644\u0644\u0623\u0642\u0644</option>",
              "'selected' : '' }}>{{ __('shop.price_high_to_low') }}</option>")
c = c.replace("'selected' : '' }}>\u0627\u0644\u0623\u0639\u0644\u0649 \u062a\u0642\u064a\u064a\u0645\u0627\u064b</option>",
              "'selected' : '' }}>{{ __('shop.highest_rated') }}</option>")
c = c.replace("'selected' : '' }}>\u0627\u0644\u0623\u0643\u062b\u0631 \u0645\u0628\u064a\u0639\u0627\u064b</option>",
              "'selected' : '' }}>{{ __('shop.best_selling') }}</option>")

# Show filters button
c = c.replace("<span class=\"font-semibold text-brand-dark\"><i class=\"bi bi-funnel-fill mr-2\"></i> \u0639\u0631\u0636 \u0627\u0644\u0641\u0644\u0627\u062a\u0631</span>",
              "<span class=\"font-semibold text-brand-dark\"><i class=\"bi bi-funnel-fill mr-2\"></i> {{ __('shop.show_filters') }}</span>")

# Mobile filter header title
c = c.replace("<h3 class=\"text-base font-bold\">\u0627\u0644\u0641\u0644\u0627\u062a\u0631</h3>",
              "<h3 class=\"text-base font-bold\">{{ __('shop.filters') }}</h3>")

# Apply button in mobile header
c = c.replace("class=\"text-sm font-semibold px-3 py-1.5 rounded-full bg-[var(--primary-color)] text-white hover:opacity-90\">\n                        \u062a\u0637\u0628\u064a\u0642",
              "class=\"text-sm font-semibold px-3 py-1.5 rounded-full bg-[var(--primary-color)] text-white hover:opacity-90\">\n                        {{ __('common.apply') }}")

# Matched brands heading
c = c.replace("                                \u0627\u0644\u0639\u0644\u0627\u0645\u0627\u062a \u0627\u0644\u062a\u062c\u0627\u0631\u064a\u0629 \u0627\u0644\u0645\u0637\u0627\u0628\u0642\u0629",
              "                                {{ __('shop.matching_brands') }}")

# Matched categories heading
c = c.replace("                                \u0627\u0644\u0623\u0642\u0633\u0627\u0645 \u0627\u0644\u0645\u0637\u0627\u0628\u0642\u0629",
              "                                {{ __('shop.matching_sections') }}")

# Products heading
c = c.replace("                            \u0627\u0644\u0645\u0646\u062a\u062c\u0627\u062a\n                        </h2>",
              "                            {{ __('shop.products') }}\n                        </h2>")

# Out of stock badge (in product card)
c = c.replace("                <span class=\"text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1\">\n                \u0645\u0646\u062a\u0647\u064a \u0627\u0644\u0643\u0645\u064a\u0629\n            </span>",
              "                <span class=\"text-white font-bold tracking-wider text-sm border border-white/50 rounded-full px-3 py-1\">\n                {{ __('common.out_of_stock') }}\n            </span>")

# Rating title
c = c.replace('title="\u062a\u0642\u064a\u064a\u0645 {{ $avg }} \u0645\u0646 5"',
              'title="{{ __(\'common.rating\') }} {{ $avg }}"')

# Currency
c = c.replace("}} \u062f.\u0639</div>", "}} {{ __('common.currency') }}</div>")

# Add to cart button spans (in shop product card)
c = c.replace("<span x-show=\"!added && !loadingAdd\"><i class=\"bi bi-cart-plus\"></i> \u0623\u0636\u0641 \u0644\u0644\u0633\u0644\u0629</span>",
              "<span x-show=\"!added && !loadingAdd\"><i class=\"bi bi-cart-plus\"></i> {{ __('common.add_to_cart') }}</span>")
c = c.replace("<span x-show=\"added\"><i class=\"bi bi-check-lg\"></i> \u062a\u0645\u062a \u0627\u0644\u0625\u0636\u0627\u0641\u0629</span>",
              "<span x-show=\"added\"><i class=\"bi bi-check-lg\"></i> {{ __('common.added_to_cart') }}</span>")

# Out of stock disabled button
c = c.replace("                                    <button class=\"btn-primary bg-gray-400 hover:bg-gray-400 cursor-not-allowed w-full\" disabled>\n                                        \u0645\u0646\u062a\u0647\u064a \u0627\u0644\u0643\u0645\u064a\u0629\n                                    </button>",
              "                                    <button class=\"btn-primary bg-gray-400 hover:bg-gray-400 cursor-not-allowed w-full\" disabled>\n                                        {{ __('common.out_of_stock') }}\n                                    </button>")

# Empty results
c = c.replace("                            \u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0646\u062a\u062c\u0627\u062a \u0645\u0637\u0627\u0628\u0642\u0629 \u0644\u0639\u0628\u0627\u0631\u0629 \u0627\u0644\u0628\u062d\u062b: <strong>",
              "                            {{ __('shop.no_results_for_term') }} <strong>")
c = c.replace("                            \u0644\u0627 \u062a\u0648\u062c\u062f \u0645\u0646\u062a\u062c\u0627\u062a \u0645\u0637\u0627\u0628\u0642\u0629 \u062d\u0627\u0644\u064a\u0627\u064b.",
              "                            {{ __('shop.no_results_currently') }}")

# CTA section
c = c.replace("<h3 class=\"text-lg font-extrabold text-gray-900 dark:text-gray-100\">\u0644\u0645 \u062a\u062c\u062f \u0627\u0644\u0645\u0646\u062a\u062c \u0627\u0644\u0630\u064a \u062a\u0628\u062d\u062b \u0639\u0646\u0647\u061f</h3>",
              "<h3 class=\"text-lg font-extrabold text-gray-900 dark:text-gray-100\">{{ __('shop.cant_find_product') }}</h3>")
c = c.replace("          \u0627\u0637\u0644\u0628 \u0627\u0644\u0645\u0646\u062a\u062c \u0627\u0644\u0622\u0646 - \u0648\u0633\u0646\u0642\u0648\u0645 \u0628\u062a\u0648\u0641\u064a\u0631\u0647 \u0644\u0643 \u0628\u0623\u0641\u0636\u0644 \u0633\u0639\u0631 \u0648\u0623\u0639\u0644\u0649 \u062c\u0648\u062f\u0629.",
              "          {{ __('shop.request_product_desc') }}")
c = c.replace("<i class=\"bi bi-plus-circle\"></i> \u0637\u0644\u0628 \u0645\u0646\u062a\u062c \u063a\u064a\u0631 \u0645\u062a\u0648\u0641\u0631",
              "<i class=\"bi bi-plus-circle\"></i> {{ __('shop.request_product') }}")

# Alert error in Alpine JS
c = c.replace("alert(d.message||'\u062d\u062f\u062b \u062e\u0637\u0623 \u0645\u0627.')",
              "alert(d.message||'{{ __(`common.connection_error`) }}')")

with open(shop, 'w', encoding='utf-8') as f:
    f.write(c)

count_changes = len(original) - len(c)
print(f"shop.blade.php updated. Size diff: {abs(count_changes)} chars ({'added' if count_changes < 0 else 'reduced'})")
