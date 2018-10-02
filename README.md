
# Documentation

## Using the standalone build.

### Examples

#### Example 1
#Example

```bash

php main download:partial -s 'http://785e6149.bwtest-aws.pravala.com/384MB.jar' -d 4mb -c 1mb

```

#### Example 2
#Example

```bash

php main download:partial -s https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf -d 4mb -c 1mb -p ./storage/dummy.pdf

```

## Using the repository.

### Installation

```bash

git clone https://github.com/jvrsolis/multi-get.git multi-get
cd multi-get
composer install

```

### Examples

#### Example 1
#Example

```bash

php main download:partial -s 'http://785e6149.bwtest-aws.pravala.com/384MB.jar' -d 4mb -c 1mb

```

#### Example 2
#Example

```bash

php main download:partial -s https://www.w3.org/WAI/ER/tests/xhtml/testfiles/resources/pdf/dummy.pdf -d 4mb -c 1mb

```
