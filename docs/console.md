
The dumbflower console enables one to apply filters to and resize images on the fly - in an ad-hoc fashion. Depending on a user's preference, said actions are applicable on a single file or multiple new files in a directory. The standard format of the console is as follows:

```
vendor/bin/dumbflower <command> <args> --src=<file> --out=<file>
```

## Commands

The command argument is the quintessential console requirement. Without it, no processing can occur. Commands in the dumbflower console correspond to library-supported actions. Actions are either GD-supported image filters or resize operations. Below is a table whose gist is a classification of library actions:

| Command    | Type					 | 
| -----------| ----------------------|
| grayscale  | GD-filter			 |
| smoothen   | GD-filter			 |
| gaussian   | GD-filter			 |
| colorize   | GD-filter			 |
| brightness | GD-filter			 |
| emboss     | GD-filter			 |
| blur       | GD-filter 			 |
| negate     | GD-filter             |
| contrast   | GD-filter             |
| resize     | GD-imagecopyresampled |

## Args

The command parameters, args for short, are the filter or resize constraints for an image. As far as the console is concerned, these parameters appear as an array without any spaces between elements. Information on the number of arguments per filter is available on the [PHP website](http://php.net/manual/en/function.imagefilter.php) however, the table below suffices as a representation of said mapping.

| Command    | Number of Arguments | Specifics 						|				
| -----------| --------------------| -------------------------------|
| grayscale  | 0 				   | - 								|
| smoothen   | 1 				   | Any float 						|
| gaussian   | 0 				   | - 								|
| colorize   | 3	 			   | ```-255``` - ```255``` for RGB |
| brightness | 1			       | ```-255``` - ```255``` 		|
| emboss     | 0			       | - 								|
| blur       | 0 			       | - 								|
| negate     | 0                   | - 								|
| contrast   | 1                   | ```-100``` - ```100```         |
| resize     | 2 				   | ```integer``` - ```integer```  |

***Note:*** In situations devoid of parameters, an empty array is the automatic result. Commands like grayscale and blur do not require parameter specification. 

### src 
src, a moniker of source file, is the file intended for modification.

### out
out, like src, is also a moniker - short for the output file.

## Examples

Applying a red filter:
```
vendor/bin/dumbflower colorize [255,0,0] --src=foo.png --out=bar.png
```

Resizing an image:
```
vendor/bin/dumbflower resize [250,125] --src=foo.png --out=bar.png
```

## Directories

The dumflower console is capable of asynchronously watching the file system. The implied potency is directory monitoring - file filtering is a possibility via an auxiliary command. Dumbflower modifies all new files added to a directory.  The standard format for directory watching is:

```
vendor/bin/dumbflower watch --dir=<folder> --acmd=<command> <args>
```

### dir

Short for directory. It works exclusively with the watch command.

### acmd

Short for auxiliary command, the acmd option only works with the watch command.

## Example

Apply a brightness filter of -30 to every image added to the ```img``` directory.
```
vendor/bin/dumbflower watch --dir=img --acmd=brightness [-30]
```