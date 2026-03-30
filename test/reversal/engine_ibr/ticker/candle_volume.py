# Import
import asyncio, json, os, sys
from ib_insync import IB, Stock
from datetime import datetime
from zoneinfo import ZoneInfo
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'common')))
from library import *

# Compute
async def Compute():

    # Mute
    mute = Mute()

    # Ibr
    ibr = IB()

    # Try
    try:

        # Set
        host, port, user, id_client, ticker, tf = (
            str(sys.argv[1]),
            int(sys.argv[2]),
            str(sys.argv[3]),
            int(sys.argv[4]),
            str(sys.argv[5]),
            int(sys.argv[6])
        )

        # Connect
        await ibr.connectAsync(
            host     = host,
            port     = port,
            clientId = id_client,
            timeout  = 2
        )

        # Ticker
        ticker = Stock(ticker, 'SMART', 'USD', primaryExchange = 'NASDAQ')
        await ibr.qualifyContractsAsync(ticker)

        # Timezone
        tz_ny = ZoneInfo('America/New_York')
        tz_rome = ZoneInfo('Europe/Rome')

        # Data range
        data_range = f"{(((tf * 2000) + 1439) // 1440) + int(max(2, 6 - tf * 0.4))} D"

        # Tf
        tf = f"{tf} min{'' if tf == 1 else 's'}"

        # Data
        data = await ibr.reqHistoricalDataAsync(
            ticker,
            endDateTime    = '',
            durationStr    = data_range,
            barSizeSetting = tf,
            whatToShow     = 'TRADES',
            useRTH         = False,
            formatDate     = 1
        )

        # Result
        #result = [{
        #        'open'   : a.open,
        #        'high'   : a.high,
        #        'low'    : a.low,
        #        'close'  : a.close,
        #        'volume' : a.volume,
        #        'date'   : a.date.replace(tzinfo=tz_ny).astimezone(tz_rome).replace(second=0, microsecond=0).strftime('%Y-%m-%d %H:%M:%S')}
        #    for a in data
        #][::-1]
        
        # Result
        result = [{
                'volume' : int(a.close * a.volume),
                'date'   : a.date.replace(tzinfo=tz_ny).astimezone(tz_rome).replace(second=0, microsecond=0).strftime('%Y-%m-%d %H:%M:%S')}
            for a in data
        ][::-1]        

        # Return
        print(json.dumps(result), flush=True)

    # Finally
    finally:

        # Mute
        Mute(mute)

        # Disconnect
        ibr.disconnect()

# Exec
if __name__ == '__main__':
    asyncio.run(Compute())