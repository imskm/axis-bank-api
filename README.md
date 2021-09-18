# A Local Database Address API PHP Composer Package
This package solves a specific scenario when a particular database has address table (the entire pincode, state and districts) which contains a very large set of records realted to address and you don't want to create multipe database with that huge number of address records table for every project you do.
This address table only contains states, districts of states and towns with pincodes of that states. And this table provide easy lookup of town, district and sate of a pincode.
This package can also be used in production environment where you don't want to store towns, districts and states of entire country for every project in every database you create on shared hosting server.
So this package can help in this situation.
It solves this duplicate database problem (for no reason) by allowing to use the only database which has address tables, and this package provides a nice api to fetch, lookup addresses using `pincode` and `town_id`.
This way you don't have to create copy of address tables for every project in every database.

## Create object
```php
use LocalAddressApi\AddressApi;

// $pincode is optional, useful when you want to fetch single town by id
$address = new AddressApi($host, $port, $db_name, $username, $password, $pincode);
```

## Fetch towns of a pincode
```php
$towns = $address->towns();
```

## Fetch single town by town id
```php
use LocalAddressApi\AddressApi;

$town_id = 100;
// Notice $pincode is not given as last argument
$address = new AddressApi($host, $port, $db_name, $username, $password);
$town = $address->town($town_id);
```

## Fetch district of the town
```php
$district = $town->district();
```

## Fetch state of the town
```php
$state = $town->state();
```

## Fetch state of a district using district object
```php
$district = $town->district();
$state = $district->state();
```
