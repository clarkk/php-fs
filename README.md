# php-fs
A small set of tools to handle the file system with file structure, disk drive maintenance etc.

## \FS\Structure
A good practice to manage data files with relation to an id in a database is **not** to keep too many files in each directory. This will structure all files and directories, so you never get more than 100, 1,000 or 10,000 files in each directory depending on your setup.

### Create directory structure with maximum 100 files in each directory
```
$file_id     = 4182983;
$file_name   = $file_id.'.png';
$base_path   = '/var/www/images';
$file_path   = (new \FS\Structure($file_id, $base_path))->create(2);

echo $file_path.'/'.$file_name;
```
The above will generate `/var/www/images/4000000/100000/80000/2000/900/4182983.png`;

### Create directory structure with maximum 1,000 files in each directory
```
$file_id     = 4182983;
$file_name   = $file_id.'.png';
$base_path   = '/var/www/images';
$file_path   = (new \FS\Structure($file_id, $base_path))->create(3);

echo $file_path.'/'.$file_name;
```
The above will generate `/var/www/images/4000000/100000/80000/2000/4182983.png`;

### Get (without creating) directory structure with maximum 100 files in each directory
```
$file_id     = 4182983;
$file_name   = $file_id.'.png';
$base_path   = '/var/www/images';
$file_path   = (new \FS\Structure($file_id, $base_path))->get(2);

echo $file_path.'/'.$file_name;
```
The above will generate (without creating each directory) `/var/www/images/4000000/100000/80000/2000/900/4182983.png`;

## \FS\Purge_structure
### Purge a file structure path and delete empty directories in the path
```
$file_path = '/var/www/images/4000000/100000/80000/2000';
\FS\Purge_structure::purge($file_path);
```
The above will delete all directories in the structure path (4000000/100000/80000/2000) until a non-empty directory is meet
