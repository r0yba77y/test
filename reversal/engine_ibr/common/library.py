# Import
import asyncio, json, socket, sys
from ib_insync import IB

# Ibr
async def Ibr(timeout = 2):

    # Compute
    result = IB()
    result.RequestPositions = False
    result.RequestAccountUpdates = False

    # Try
    try:

        # Compute
        await result.connectAsync('localhost', int(sys.argv[2]), clientId = int(sys.argv[3]), timeout = timeout)

    # Except
    except Exception:

        # Return
        return

    # Return
    return result

def Ibr(port = 4001, clientId = 1, timeout = 2):

    # Ibr
    ibr = IB()
    ibr.RequestPositions = False
    ibr.RequestAccountUpdates = False

    async def connect():
        await ibr.connectAsync('localhost', port, clientId = clientId, timeout = timeout)
        return ibr

    # Compute
    loop = asyncio.new_event_loop()
    asyncio.set_event_loop(loop)

    # Try
    try:
        return loop.run_until_complete(connect())

    # Finally
    finally:
        loop.close()

# Mute
class MuteClass:
    def write(self, msg): pass
    def flush(self): pass


# Mute
def Mute(data = None):

    # Off
    if data:
        sys.stderr = data

    # On
    else:
        data = sys.stderr
        sys.stderr = MuteClass()

    # Return
    return data

# Json
def Json(data):

    # Print
    print(json.dumps(data, separators = (',', ':')), flush = True)