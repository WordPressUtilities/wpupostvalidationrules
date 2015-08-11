# WPU Post Validation Rules
Add validation rules before saving a WordPress post

## How to install
* Put this folder to your wp-content/plugins/ folder.
* Activate the plugin in "Plugins" admin section.

## How to use

Add custom filters in your functions.php code or in a (mu-)plugin, following the example below :

```php
// Prevent the word "az" in a post content
add_filter('wpupostvalidationrules_ruleslist', 'myproject_neveraz', 10, 2);
function myproject_neveraz($messages, $content) {
    if (strpos($content,'az') !== false) {
        $messages[] = 'The content should not contain az.';
    }
    return $messages;
}
```

## Roadmap
- [ ] French translation.
- [ ] Back-end validation.
- [ ] Add an (×) button to the error message.