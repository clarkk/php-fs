# php-fs
A small set of tools to handle the file system with file structure, disk drive maintenance etc.

## \FS\Structure
A good practice to manage data files with relation to an id in a database is **not** to keep too many files in each directory. Searching for files is slowed down in directories with many files. This will structure all files and directories, with no more than 100, 1,000 or 10,000 files in each directory depending on your choice.

### Create directory structure with maximum 100 files in each directory
```
$file_id     = 4182983;
$file_name   = $file_id.'.png';
$base_path   = '/var/www/images';
$file_path   = (new \FS\Structure($file_id, $base_path))->create(2);

echo $file_path.'/'.$file_name;
```
The above will generate and create each directory `/var/www/images/4000000/100000/80000/2000/900/4182983.png`;

### Create directory structure with maximum 1,000 files in each directory
```
$file_id     = 4182983;
$file_name   = $file_id.'.png';
$base_path   = '/var/www/images';
$file_path   = (new \FS\Structure($file_id, $base_path))->create(3);

echo $file_path.'/'.$file_name;
```
The above will generate and create each directory `/var/www/images/4000000/100000/80000/2000/4182983.png`;

### Get (without creating) directory structure with maximum 100 files in each directory
```
$file_id     = 4182983;
$file_name   = $file_id.'.png';
$base_path   = '/var/www/images';
$file_path   = (new \FS\Structure($file_id, $base_path))->get(2);

echo $file_path.'/'.$file_name;
```
The above will generate (without creating each directory) `/var/www/images/4000000/100000/80000/2000/900/4182983.png`;

### Purge a file structure path and delete empty directories in the path
```
$file_path = '/var/www/images/4000000/100000/80000/2000';
\FS\Structure::purge($file_path);
```
The above will delete all directories in the structure path `4000000/100000/80000/2000` until a non-empty directory is met

## \FS\Tmp_path
Create a temporary directory if you need to process files.

### Create a temporary directory
```
$file_id     = 4182983;
$base_path   = '/var/www/tmp';
$Tmp         = new \FS\Tmp_path($file_id, $base_path);
$tmp_path    = $Tmp->create('test');

//  Process some files in the tmp directory
file_put_contents("$tmp_path/test.txt", 'test');

//  Delete all files and tmp directory after use
$Tmp->purge(true);

echo $tmp_path;
```

The above will create and delete directory `/var/www/tmp/2022-04-02-133836_624835bc854b9_test_4182983`
