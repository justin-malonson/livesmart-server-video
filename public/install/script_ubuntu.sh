#!/bin/bash

sudo npm install
sudo npm install -g pm2
sudo apt install -y python3.8 python3-pip
sudo pm2 start src/livesmart.js
sudo pm2 startup
sudo pm2 save