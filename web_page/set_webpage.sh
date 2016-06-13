sudo cp /var/www/showdata.json /home/pi/PiDrive/webpage/backup-show-data/
sudo rm /var/www/*
sudo cp /home/pi/PiDrive/webpage/$1/* /var/www/
sudo chmod 666 /var/www/*
