Multilingual Options Page
================
Create multilingual ACF Options Pages.

The plugin creates a separate options subpage for each language. Works with multisite also when sites have different languages active.

Usage:
```
$multilingual_options_page = FrcMultilingualOptionsPage::get_instance();
$multilingual_options_page->add_page($title, $parent_slug);
```

E.g.
```
$multilingual_options_page->add_page('Footer', 'site-settings');
```
If active languages are Finnish and English, the plugin will create two options subpages: `Footer FI` and `Footer EN` under `site-settings` parent. When creating parent, slug is defined with `menu_slug`.
