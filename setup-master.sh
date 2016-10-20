#!/bin/sh

adduser --disabled-password --gecos "" vmaster

cat > /home/vmaster/.bash_profile <<EOF
# .bash_profile

# Get the aliases and functions
if [ -f ~/.bashrc ]; then
	. ~/.bashrc
fi

# User specific environment and startup programs

PATH=\$PATH:\$HOME/bin

export PATH

export VUFIND_HOME=/home/\$USER/vufind
export JAVA_OPTIONS="-server -Dmaster.enable=true  -Xmx1g -Xss256k -Djava.awt.headless=true -XX:+UseParNewGC -XX:+UseConcMarkSweepGC -XX:CMSInitiatingOccupancyFraction=75 -XX:+UseCMSInitiatingOccupancyOnly"
export JETTY_PORT=8080
export JETTY_PID=/tmp/\$USER.pid
export JETTY_CONSOLE=/dev/null

EOF

cat > /etc/systemd/system/vmaster.service <<EOF
[Unit]
Description=VuFind Solr Master
After=network.target

[Service]
Type=forking
ExecStart=/home/vmaster/vufind/vufind.sh start
ExecStop=/home/vmaster/vufind/vufind.sh stop
User=vmaster
Group=vmaster
PIDFile=/tmp/vmaster.pid

[Install]
WantedBy=multi-user.target
EOF

git clone --depth 1 https://github.com/yorkulibraries/vufind.git /home/vmaster/vufind
chown -R vmaster:vmaster /home/vmaster

systemctl daemon-reload
systemctl enable vmaster
systemctl start vmaster

