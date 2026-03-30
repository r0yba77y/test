# Import
import asyncio, json, os, sys
from ib_async import IB, ExecutionFilter
from datetime import date
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'common')))
from library import *

# Set
host, port, user, id_client = (
    str(sys.argv[1]),
    int(sys.argv[2]),
    str(sys.argv[3]),
    int(sys.argv[4])
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

        # Data
        data = await ibr.reqExecutionsAsync(
            ExecutionFilter(
                time = date.today().strftime('%Y%m%d') + ' 00:00:00'
            )
        )

        # Wait
        timeout = 2
        while timeout > 0:
            if all(f.commissionReport.commission != 1.7976931348623157e+308 for f in ibr.fills()):
                break
            await ibr.waitOnUpdate(0.5)
            timeout -= 0.5

        # Result
        result = {}

        # Compute
        for e in ibr.fills():

            # Continue ?
            if e.execution.orderId == 0:
                continue

            # Fee
            fee = e.commissionReport.commission if e.commissionReport.commission != 1.7976931348623157e+308 else 0.0

            # Trade
            trade = {
                'cluster'  : e.execution.orderId,
                'price'    : e.execution.avgPrice,
                'quantity' : e.execution.shares,
                'fee'      : round(fee, 4),
                'action'   : 'buy' if e.execution.side.lower() in ['bot', 'buy'] else 'sell',
                'date'     : e.execution.time.strftime("%Y-%m-%d %H:%M:%S")
            }

            # Ticker
            ticker = e.contract.symbol

            # Result
            result.setdefault(e.execution.acctNumber, {}).setdefault(ticker, {})

            # Call
            call = e.execution.permId

            # Compute
            if call in result[e.execution.acctNumber][ticker]:

                # Existing
                existing = result[e.execution.acctNumber][ticker][call]

                # Quantity
                total_qty = existing['quantity'] + trade['quantity']

                # Price
                if total_qty > 0:
                    existing['price'] = (existing['price'] * existing['quantity'] + trade['price'] * trade['quantity']) / total_qty

                # Quantity
                existing['quantity'] = total_qty

                # Fee
                existing['fee'] = round(existing['fee'] + trade['fee'], 4)

                # Date
                existing['date'] = trade['date']

            else:

                # Result
                result[e.execution.acctNumber][ticker][call] = trade

        # Result
        result = {user: result.get(user, {})}

        # Return
        print(json.dumps(result, separators=(',', ':'), ensure_ascii=False), flush=True)

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