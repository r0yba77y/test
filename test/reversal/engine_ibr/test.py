# Import
import asyncio, os, sys
sys.path.append(os.path.abspath(os.path.join(os.path.dirname(__file__), '..', 'common')))
from library import *

# Compute
async def Compute():

    # Mute
    mute = Mute()

    # Ibr
    ibr = None

    # Compute
    try:

        # Ibr
        ibr = await Ibr()

        # Data
        data = ibr.managedAccounts()[0]
        data = ibr.accountValues(data)

        # Return
        for a in data:
            if a.tag in ['AvailableFunds', 'BuyingPower', 'GrossPositionValue']:
                print(a.value)

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