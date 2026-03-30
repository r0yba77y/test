# Import
import yfinance as yf

# Return
print(round(yf.Ticker("EURUSD=X").fast_info["lastPrice"], 4))