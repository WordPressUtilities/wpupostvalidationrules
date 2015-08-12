# WPU Post Validation Rules

Add custom validation rules before saving a WordPress post : prevent the use of a word, too many links, too few images, etc.
Translated in English, French, Spanish, Italian, Deutsch.

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
- [ ] Back-end validation.
- [ ] Limit to a special post type.
- [ ] Add a level for each rule.
- [x] French, Spanish, Italian, Deutsch translation.
- [x] Add an (×) button to the error message.