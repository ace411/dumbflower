<div style="display: flex; flex-wrap: nowrap; flex-flow: column; align-content: center; align-items: center; justify-content: center;">
    <img src="https://ucarecdn.com/43f41248-6a21-4428-88b7-084e1f13e050/dumbflowerlogo480x270.png" alt="dumbflower logo">
    <div style="display: flex; flex-flow: row; height: 20px; flex-wrap: nowrap; margin-bottom: 1.4rem;">
        <img alt="Build Status" style="max-width: 105px; margin-left: 2px;" onClick="location.href='https://travis-ci.org/ace411/dumbflower'" src="https://travis-ci.org/ace411/dumbflower.svg?branch=master">
        <img alt="Codacy Badge" style="max-width: 105px; margin-left: 2px;" onClick="location.href='https://www.codacy.com/app/ace411/dumbflower?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ace411/dumbflower&amp;utm_campaign=Badge_Grade'" src="https://api.codacy.com/project/badge/Grade/86961fde07564ec388c4a93582f6ba7a">
        <img alt="License" style="max-width: 105px; margin-left: 2px;" onClick="location.href='https://packagist.org/packages/chemem/dumbflower'" src="https://poser.pugx.org/chemem/dumbflower/license">
    </div>
</div>

DumbFlower is a simple image manipulation library for PHP. It is an appendage of the GD library, a component of the PHP userland core. The subsequent text is documentation of the library which should help you, the reader, understand how to go about using it.

# Installation

Before you can use the dumbflower library, you should have either Git or Composer installed on your system of preference. To install the package via Composer, type the following in your preferred command line interface:

```
composer require chemem/dumbflower dev-master
```


# Usage
 
 The DumbFlower package, like many PHP libraries, is namespaced. The table below is an enumeration of the library's function groupings and respective namespaces:

| Type             | Namespace                         |
|------------------|-----------------------------------|
| Filters          | ```Chemem\DumbFlower\Filters\```  |
| Resize functions | ```Chemem\DumbFlower\Resize\```   |
| Snapshots        | ```Chemem\DumbFlower\Snapshot\``` |

# Supported Formats

Since dumbflower is an embellishment of the PHP GD image extension, it supports most image formats included in the [extension's documentation](http://php.net/manual/en/book.image.php). Just in case you do not remember what those extensions are, the list below is a reminder:

- ```jpeg```

- ```gif```

- ```png```

- ```webp```
