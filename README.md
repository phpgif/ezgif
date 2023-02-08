<div align="center">

<img src="https://user-images.githubusercontent.com/76186054/196006381-dd8a95b5-4826-432b-9ed3-ec71bf1f544f.png" width="150px">


# EZGif

## What is EZGif?

EZGif is the simplest way to generate GIFs. It's powered by [phpgif/phpgif](https://github.com/phpgif/phpgif).

## Installing phpgif

### Using Composer

Run the following command in Terminal or Command Prompt:

<div align="left">

```
composer require phpgif/ezgif
```

```php
use phpgif\EZGif\EZGif
```
	
</div>

	

### Without Composer

When using shared hosting, many hosting providers don't allow Composer. Use the following system to install PHP-GIF-Reborn.


<div align="left">


1. Download the repo
2. Open the src/EZGif folder
3. Copy EZGif.standalone.php onto your webserver
4. Paste the following code at the top of your file:

	

```php
include 'EZGif.standalone.php';
```

	
</div>
	
	
## Getting Started

Create a new PHP file. Include this at the top:

<div align="left">


```php
<?php
use phpgif\EZGif\EZGif;
$ez = new EZGif();
```


</div>


## Usage

### `generateFromDir`

This function will generate a GIF from a directory!

```php
$ez->generateFromDir(string $directory, int $delay, array? $filetypes = ['.png', '.jpg', '.jpeg', '.gif', '.tiff', '.bmp', '.ico'])
```

#### Example

```php
$ez->generateFromDir('imgs');
```
** IMPORTANT: For this to work, you must use the `displayGif` and `setHeaders` methods. ***

### `fileListGif`

Generate a GIF from a list of files

```php
$ez->generateFileList(array $files, int? $delay = 100)
```

#### Example

```php
$ez->generateFileList(['image.png', 'picture.jpg'], 500);
```

### `setHeaders`

This function will set the headers to prepare the user's browser to render the GIF.

```php
$ez->setHeaders(bool? $disableCache = true, bool? $setContentType = true)
```

#### Example

```php
$ez->setHeaders(false) # Enable caching!
```

### `displayGif`

This function will show the GIF on the webpage, for the user to render. Please note that this function will call `exit` when it completes. This function takes no parameters.

```php
$ez->displayGif()
```

#### Example

```php
$ez->displayGif();
```

### `toFile`

This function will export the GIF to a file. This function does not require headers to be set.

```php
$ez->toFile(string $fileName)
```

#### Example

```php
$ez->toFile('example.gif');
```



## License & Credits

This software is published under the MIT License.
###### phpgif
This library uses [phpgif/phpgif](https://github.com/phpgif/phpgif).
###### GIFEncoder
GIFEncoder.class.php is adapted from the GIFEncoder PHP class by László Zsidi.

&copy; 2023 mrfakename. All rights reserved.
</div>
