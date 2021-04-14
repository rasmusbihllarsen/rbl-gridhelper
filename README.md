# Gridhelper
Makes it easy to create grids.

## How to show a grid
Desktop-grid: `gridhelper();`   
Mobile-grid: `mobile_grid();`

## Actions
`gridhelper_custom_content`   
Has the post as a paramter.   
Can be used to add content to `.grid__inner`.

## Add posttypes
Add the following in your `functions.php`-file:
```php
global $gridhelper_posttypes;
$gridhelper_posttypes = array(
  'post'  => 'Post'
);
```
