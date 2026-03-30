# Import
import os, sys
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
        host, port, user, id_client, ticker = (
            str(sys.argv[1]),
            int(sys.argv[2]),
            str(sys.argv[3]),
            int(sys.argv[4]),
            str(sys.argv[5])
        )

        # Connect
        await ibr.connectAsync(
            host     = host,
            port     = port,
            clientId = id_client,
            timeout  = 2
        )

        # Compute
        ibr.cancelOrder(Order(
            orderId = orderId,
            account = user
        ))

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