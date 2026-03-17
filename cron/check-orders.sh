// path: cron/check-orders.sh
#!/bin/bash
# Check order status every 5 minutes

LOG_FILE="/var/log/check-orders.log"
ERROR_LOG="/var/log/check-orders-error.log"

while true; do
    echo "Starting order check at $(date)" >> $LOG_FILE
    
    /usr/bin/curl -s -o /dev/null -w "%{http_code}" https://yourdomain.com/ajax/check-orders.php >> $LOG_FILE 2>> $ERROR_LOG
    
    if [ $? -ne 0 ]; then
        echo "Order check failed at $(date)" >> $ERROR_LOG
    fi
    
    sleep 300
done