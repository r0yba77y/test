# Import
import asyncio, os, sys, re, json
from datetime import datetime, date
from ib_insync import IB, Stock, StopOrder
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
        host, port, user, id_client, ticker, price, action, cluster = (
            str(sys.argv[1]),
            int(sys.argv[2]),
            str(sys.argv[3]),
            int(sys.argv[4]),
            str(sys.argv[5]),
            float(sys.argv[6]),
            str(sys.argv[7]),
            int(sys.argv[8])
        )
        # Connect
        await ibr.connectAsync(
            host     = host,
            port     = port,
            clientId = id_client,
            timeout  = 2
        )

        # Ticker
        ticker = Stock(ticker, 'SMART', 'USD')
        await ibr.qualifyContractsAsync(ticker)

        # Parameter
        parameter = StopOrder(
            account       = user,
            stopPrice     = price,
            action        = action,
            tif           = 'GTC',
            outsideRth    = True,
            parentId      = cluster,
            ocaGroup      = f'OCA_{cluster}',
            ocaType       = 1
        )

        # Result
        result = await ibr.placeOrderAsync(ticker, parameter)
        timeout = 10 
        while not result.orderStatus.status and timeout > 0:
            await asyncio.sleep(0.1)
            timeout -= 1

        # Status
        status = (re.sub(r'(?<!^)(?=[A-Z])', '_', result.orderStatus.status).lower().replace('inactive', 'rejected'))

        # Result
        result = {
            'call'   : result.order.permId or 0,
            'status' : status
        }

        # Return
        print(json.dumps(result, separators = (',', ':')), flush = True)

    # Except
    except Exception:

        # Return
        return

    # Finally
    finally:

        # Mute
        Mute(mute)

        # Disconnect
        if ibr.isConnected():
            ibr.disconnect()

# Exec
if __name__ == '__main__':
    asyncio.run(Compute())