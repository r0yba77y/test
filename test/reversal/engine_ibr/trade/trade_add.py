# Import
import asyncio, json, os, sys
from ib_insync import IB, Stock, LimitOrder, MarketOrder
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
        host, port, user, id_client, ticker, price, quantity, action = (
            str(sys.argv[1]),
            int(sys.argv[2]),
            str(sys.argv[3]),
            int(sys.argv[4]),
            str(sys.argv[5]),
            float(sys.argv[6]),
            float(sys.argv[7]),
            str(sys.argv[8])
        )
        
        # Connect
        await ibr.connectAsync(
            host     = host,
            port     = port,
            clientId = id_client,
            timeout  = 2
        )

        # Ticker
        ticker = Stock(ticker, 'SMART', 'USD', primaryExchange='NASDAQ')
        await ibr.qualifyContractsAsync(ticker)

        # Parameter
        if price > 0:
            parameter = LimitOrder(
                account       = user,
                action        = action,
                totalQuantity = quantity,
                lmtPrice      = price,
                tif           = 'GTC',
                outsideRth    = True
            )
        else:
            parameter = MarketOrder(
                account       = user,
                action        = action,
                totalQuantity = quantity,
                tif           = 'GTC',
                outsideRth    = True
            )

        # Data
        data = ibr.placeOrder(ticker, parameter)

        # Result
        while data.order.orderId == -1 or data.orderStatus.status in ('PendingSubmit', 'PreSubmitted'):
            await asyncio.sleep(0.1)

        # Result
        result = {
            'cluster' : data.order.orderId,
            'call'    : data.order.permId or 0,
            'status'  : ''.join(['_' + c.lower() if c.isupper() else c for c in data.orderStatus.status]).lstrip('_')
        }

        # Return
        print(json.dumps(result, separators=(',', ':')), flush=True)

    # Except
    except Exception as e:

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