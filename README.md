# laravel-lakala

## install 
```
composer require fast-elephant/laravel-lakala
```

## usage

publish
```
php artisan vendor:publish --provider="FastElephant\LaravelLakala\LaravelLakalaServiceProvider"
```

usage
```
$lakala = new \FastElephant\LaravelLakala\Lakala();
$lakala->post('/ccss/counter/order/create', [
    'out_order_no' => '12345678',
    'merchant_no' => '822100041120005',
    'amount' => 200,
]);
```