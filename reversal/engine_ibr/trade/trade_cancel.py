# Import
import asyncio, os, sys
from datetime import datetime, date
from ib_insync import IB, Order
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
        host, port, user, id_client, orderId = (
            str(sys.argv[1]),
            int(sys.argv[2]),
            str(sys.argv[3]),
            int(sys.argv[4]),
            int(sys.argv[5])
        )

        # Connect
        await ibr.connectAsync(
            host     = host,
            port     = port,
            clientId = id_client,
            timeout  = 2
        )

        # Sync
        sync = await ibr.reqAllOpenOrdersAsync()

        # Result
        for a in sync:
            if a.order.orderId == orderId and a.order.account == user:
                ibr.cancelOrder(a.order)
                break

        # Sleep
        await asyncio.sleep(1)

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