
Genealogical tree
=================

Setup app container:

```bash

$ # 1. clone repo
$ git clone https://github.com/ivan1993spb/genealogical-tree.git

$ # 2. build image
$ cd genealogical-tree
$ docker build -t "genealogical-tree" .

$ # 3. run container
$ gtree="$(docker run -d -p 80:80 genealogical-tree)"

```

Than open [http://localhost](http://localhost)
