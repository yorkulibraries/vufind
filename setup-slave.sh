#!/bin/sh

adduser --disabled-password --gecos "" vslave

cat > /home/vslave/.bash_profile <<EOF
# .bash_profile

# Get the aliases and functions
if [ -f ~/.bashrc ]; then
	. ~/.bashrc
fi

# User specific environment and startup programs

PATH=\$PATH:\$HOME/bin

export PATH

export MASTER_URL=http://localhost:8080/solr

export VUFIND_HOME=/home/\$USER/vufind
export JAVA_OPTIONS="-server -Dslave.enable=true -Dmaster.url=\$MASTER_URL  -Xmx1g -Xss256k -Djava.awt.headless=true -XX:+UseParNewGC -XX:+UseConcMarkSweepGC -XX:CMSInitiatingOccupancyFraction=75 -XX:+UseCMSInitiatingOccupancyOnly"
export JETTY_PORT=8081
export JETTY_PID=/tmp/\$USER.pid
export JETTY_CONSOLE=/dev/null

EOF

cat > /etc/systemd/system/vslave.service <<EOF
[Unit]
Description=VuFind Solr Master
After=network.target

[Service]
Type=forking
ExecStart=/home/vslave/vufind/vufind.sh start
ExecStop=/home/vslave/vufind/vufind.sh stop
User=vslave
Group=vslave
PIDFile=/tmp/vslave.pid

[Install]
WantedBy=multi-user.target
EOF

git clone --depth 1 https://github.com/yorkulibraries/vufind.git /home/vslave/vufind
chown -R vslave:vslave /home/vslave

systemctl daemon-reload
systemctl enable vslave
systemctl start vslave

