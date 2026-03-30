#!/bin/bash

# Wait
sleep 55
while [ $(date +%S) -ne 59 ]; do
  sleep 1
done
while [ $(date +%S) -ne 0 ]; do
  sleep 0.1
done

# Compute
for request in alive connection forex candle candle_volume trade_history trade_order maintenance; do
  timeout 60 /usr/bin/php /var/www/html/exec.php $request 2>/dev/null &
done

# End
echo 'CRON completed!'