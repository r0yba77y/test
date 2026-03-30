# Import
import asyncio, sys

# Compute
async def Compute():

    # Result
    result = 0

    # Set
    host = str(sys.argv[1])

    # Compute
    for port in (4001, 4002):

        # Try
        try:

            # Compute
            reader, writer = await asyncio.wait_for(asyncio.open_connection(host, port), timeout=2)

            # Result
            writer.close()
            await writer.wait_closed()
            result = port
            break

        # Exception
        except Exception:

            # Pass
            pass

    # Return
    print(result, flush=True)

# Exec
if __name__ == '__main__':
    asyncio.run(Compute())