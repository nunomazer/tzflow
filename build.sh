#!/bin/bash

box build -c box.json
rm bin/tzflow
mv bin/tzflow.phar bin/tzflow

echo 'DONE !!'