# Import
import asyncio, json, os, re, sys
from ib_async import IB
from datetime import datetime, date
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'common')))
from library import *

# Set
host, port, user, id_client, balance_edge, forex = (
    str(sys.argv[1]),
    int(sys.argv[2]),
    str(sys.argv[3]),
    int(sys.argv[4]),
    float(sys.argv[5]),
    float(sys.argv[6])
)

# Ibr
async def Ibr():

    # Mute
    mute = Mute()

    # Result
    result = IB()
    result.RequestPositions = False
    result.RequestAccountUpdates = False

    # Try
    try:
        
        # Connect
        await result.connectAsync(
            host     = host,
            port     = port,
            clientId = id_client,
            timeout  = 2
        )

    # Except
    except Exception:

        # Return
        return

    # Return
    return result

# Compute
async def Compute():

    # Mute
    mute = Mute()

    # Ibr
    ibr = None

    # Try
    try:

        # Ibr
        ibr = await Ibr()

        # Sleep
        await asyncio.sleep(2)

        # Result
        result = {}

        # Balance
        for a in ibr.managedAccounts():

            # Result
            result[a] = {}
            for b in ibr.accountValues(a):
                if b.tag == 'BuyingPower':
                    result[a]['available'] = float(b.value)
                elif b.tag == 'GrossPositionValue':
                    result[a]['locked'] = float(b.value)
            
            # Result
            result[a]['ticker'] = {}

        # Trade
        for a in await ibr.reqAllOpenOrdersAsync():

            # Ticker
            ticker = a.contract.symbol

            # Result
            result.setdefault(a.order.account, {}).setdefault('ticker', {}).setdefault(ticker, {
                'quantity' : 0.0,
                'position' : {'price': 0.0, 'quantity': 0.0},
                'book_in'  : {'price': 0.0, 'quantity': 0.0},
                'book_out' : {'price': 0.0, 'quantity': 0.0}
            })

            # Order_data
            order_data = {
                'price'    : getattr(a.order, 'lmtPrice', 0),
                'quantity' : getattr(a.order, 'totalQuantity', 0)
            }
            
            # Result
            if a.order.action.lower() == 'buy':
                result[a.order.account]['ticker'][ticker]['book_in'] = order_data
            else:
                result[a.order.account]['ticker'][ticker]['book_out'] = order_data

        # Position
        for a in ibr.positions():

            # Ticker
            ticker = a.contract.symbol

            # Result
            result.setdefault(a.account, {}).setdefault('ticker', {}).setdefault(ticker, {
                'quantity' : 0.0,
                'position' : {'price': 0.0, 'quantity': 0.0},
                'book_in'  : {'price': 0.0, 'quantity': 0.0},
                'book_out' : {'price': 0.0, 'quantity': 0.0}
            })

            # Result
            result[a.account]['ticker'][ticker]['position']['price'] = round(getattr(a, 'avgCost', 0), 2)
            result[a.account]['ticker'][ticker]['position']['quantity'] = round(abs(getattr(a, 'position', 0)), 4)

        # Compute
        for a in result:

            # Quantity
            for t in result[a]['ticker'].values():
                t['quantity'] = round(t.get('position', {}).get('quantity', 0.0) + t.get('book_in', {}).get('quantity', 0.0), 4)            

            # Funds
            funds_eur = round(result[a].get('available', 0.0), 2)
            funds_usd = round(result[a].get('available', 0.0) * forex, 2)

            # Balance
            balance = (result[a].get('available', 0.0) * forex) * balance_edge

            # Locked
            locked = [0, 0]
            for b in result[a]['ticker'].values():
                locked[0] += b.get('book_in', {}).get('price', 0) * b.get('book_in', {}).get('quantity', 0)
                locked[1] += b.get('position', {}).get('price', 0) * b.get('position', {}).get('quantity', 0)
            locked = locked[0] + locked[1]

            # Result
            result[a] = {
                'funds_eur' : funds_eur,
                'funds_usd' : funds_usd,
                'balance'   : round(balance, 2),
                'locked'    : round(locked, 2),
                'available' : round(balance - locked, 2),
                'ticker'    : result[a].get('ticker', {})
            }

            # Stampa
            print(json.dumps(result, separators = (',', ':'), ensure_ascii = False), flush = True)

    # Except
    except Exception:

        # Return
        return

    # Finally
    finally:

        # Mute
        Mute(mute)

        # Disconnect
        if ibr:
            ibr.disconnect()

# Exec
if __name__ == '__main__':
    asyncio.run(Compute())