# Introduction
This repository is for a console application whose purpose is to act as a utility microservice to download large files from the web. The service can resume at any point in time and has a hard php limit of 1G usage of memory. Using this service can be done through the build file found in the build folder or using the repository and hosting it on a virutal/hardware machine. The repository uses Laravel Zero, a fork of Laravel geared toward building console application.

![alt text](https://github.com/jvrsolis/multi-get/blob/master/Screen%20Shot%202018-10-02%20at%2011.34.26%20AM.png)

# Documentation

## Componenets

The application logic is divided between three classes:
```php
App\Commands\PartialDownloadCommand::class
App\Providers\DownloadServiceProvider::class
App\Services\DownloadService::class
```
### Partial Download Command Class
A command class used by the application to contain the console 
interface logic that allows a user to perform a partial download 
of a file from a remote source using the command line.

### Download Service Provider Class
A service provider class used to create a singleton DownloadService class to use
throughout the application.

### Download Service Class
A reusable service class utilized as a singleton in the console application.
Its purpose is to contain any logic associated with downloading a file.
Currently only supports downloading partial/whole files using a range request. 
However, this class has been built to accommodate other methods related to the 
downloading files at a later time. No implementation is done here. Any
implementation is extracted to points of use classes.


## Assumptions
ASSUMPTION: Assumes input for bytes are strings containing the integer value and units size. 

ASSUMPTION: Assumes local scope is output file destination  (can be made dynamic however)

ASSUMPTION: No file deletion logic is necessary.
            If a file is completely downloaded then a user must manually delete that file.

# Utilization
<b>Note</b>: If you require greater flexibility of the file destination path use the <b>repository</b> build

## Using the standalone build.

A standalone php phar application has been built with 
all dependancies in mind. Some caveats for using the standalone build include:

- Specifying all parameters
- Destination path must be relative. 
- ext-fileinfo extension must be installed and included in php.ini
- ext-posix extension must be install and included in php.ini

Additionally when specifying the download size and the chunk size please provide the units

Right now two unit systems are supported:

- The Metric system that is based on a 1000-byte kilobyte and uses standard SI suffixes (kB, MB, GB, TB, PB, …)
- The Binary system that is based on a 1024-byte kilobyte and uses binary suffixes (KiB, MiB, GiB, TiB, PiB, …)
 
<b>Command name</b>
```bash
download:partial
```

| Parameter | Name | Description | Required |
|:--|:--|:--|:--|
| -s | Source Url | The source url. | Required |
| -d | Download Size | The amount of the file to retrieve. | Required |
| -c | Chunk Size | The size of the each request. | Required |
| -p | Destination Path | The output fullname of the downloaded file. | Required |



### Examples

#### Example 1
Using the provided Test URL:

```bash

php main download:partial -s 'http://a10b57dd.bwtest-aws.pravala.com/384MB.jar' -d 4mb -c 1mb -p ./storage/dummy.pdf

```

#### Example 2
Using a sperate URL with smaller than 4mb total size:

```bash

php main download:partial -s https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf -d 4mb -c 1mb -p ./storage/dummy.pdf

```

## Using the repository.

The repository contains assumptions that the users machine has the following dependancies before using the repository contents:

- Git
- Composer
- ext-fileinfo extension must be installed and included in php.ini
- ext-posix extension must be install and included in php.ini

Additionally when specifying the download size and the chunk size please provide the units

Right now two unit systems are supported:

- The Metric system that is based on a 1000-byte kilobyte and uses standard SI suffixes (kB, MB, GB, TB, PB, …)
- The Binary system that is based on a 1024-byte kilobyte and uses binary suffixes (KiB, MiB, GiB, TiB, PiB, …)

<b>Command name</b>
```bash
download:partial
```

| Parameter | Name | Description | Required | Default |
|:--|:--|:--|:--|:--|
| -s | Source Url | The source url. | Optional | http://a10b57dd.bwtest-aws.pravala.com/384MB.jar |
| -d | Download Size | The amount of the file to retrieve. | Optional | 4mb |
| -c | Chunk Size | The size of the each request. | Optional | 1mb |
| -p | Destination Path | The output fullname of the downloaded file. | Optional | Repository's ./storage path


### Installation

```bash

git clone https://github.com/jvrsolis/multi-get.git multi-get
cd multi-get
composer install

```

### Examples

#### Example 1
Using the provided Test URL:

```bash

php main download:partial -s 'http://a10b57dd.bwtest-aws.pravala.com/384MB.jar' -d 4mb -c 1mb

```

#### Example 2
Using a sperate URL with smaller than 4mb total size:
```bash

php main download:partial -s https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf -d 4mb -c 1mb

```
