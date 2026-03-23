import re

path = r'c:\xampp\htdocs\tofof\resources\views\frontend\homepage.blade.php'
with open(path, 'r', encoding='utf-8') as f:
    c = f.read()

original_len = len(c)

# Replace currency: }} د.ع</div> -> }} {{ __('common.currency') }}</div>
c = c.replace('}} \u062f.\u0639</div>', "}} {{ __('common.currency') }}</div>")

# Replace rating title: title="تقييم {{ $avg }} من 5" -> title="{{ __('common.rating') }} {{ $avg }}"
c = c.replace('title="\u062a\u0642\u064a\u064a\u0645 {{ $avg }} \u0645\u0646 5"', "title=\"{{ __('common.rating') }} {{ $avg }}\"")

with open(path, 'w', encoding='utf-8') as f:
    f.write(c)

print(f'Done. File length changed by {len(c) - original_len} chars')
