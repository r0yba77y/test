# Import
import asyncio, json, os, sys
from ib_insync import IB, Stock
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

        # Ticker
        ticker = Stock(ticker, 'SMART', 'USD', primaryExchange = 'NASDAQ')
        await ibr.qualifyContractsAsync(ticker)

        # Data
        data = ibr.reqMktData(ticker, '', False, False)

        # Start
        start = asyncio.get_event_loop().time()

        # Compute
        while (asyncio.get_event_loop().time() - start) < 5:
            if data.bid is not None and data.ask is not None:
                if data.bid > 0 or data.ask > 0:
                    break
            await asyncio.sleep(0.1)

        # Return
        print(json.dumps({'bid': max(0, data.bid) or 0, 'ask': max(0, data.ask) or 0}), flush=True)

    # Finally
    finally:

        # Mute
        Mute(mute)        

        # Disconnect
        ibr.disconnect()

# Exec
if __name__ == '__main__':
    asyncio.run(Compute())