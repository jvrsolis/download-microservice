
# Documentation

## Assumptions
ASSUMPTION: Assumes input for bytes are strings containing the integer value and units size. 

ASSUMPTION: Assumes local scope is output file destination  (can be made dynamic however)

## Using the standalone build.

A standalone php phar application has been built with 
all dependancies in mind. Some caveats for using the standalone build include:

- Specifying all parameters
- Destination path must be relative. 

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

php main download:partial -s 'http://a10b57dd.bwtest-aws.pravala.com/384MB.jar' -d 4mb -c 1mb

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
