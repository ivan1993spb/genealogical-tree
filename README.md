
Genealogical tree
=================

```bash

$ # 1. clone code
$ git clone https://github.com/ivan1993spb/genealogical-tree.git

$ # 2. build image
$ cd genealogical-tree
$ sudo docker build -t "genealogical-tree" .

$ # 3. run container
$ gtree="$(sudo docker run -d -p 80:80 -p 3306:3306 genealogical-tree)"

```
