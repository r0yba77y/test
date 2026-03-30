using System;
using System.ComponentModel.DataAnnotations;
using System.Windows.Media;
using NinjaTrader.NinjaScript.Strategies;
using NinjaTrader.NinjaScript.Indicators;
using NinjaTrader.Cbi;

namespace NinjaTrader.NinjaScript.Strategies {

	public class CRON : Strategy {

		// Set
		private EMA ma1;
		private HMA ma2;
		private HMA slope;
		private RSI rsi;
		private bool pendinglong;
		private bool pendingshort;		
		private MACD macdIndicator;
		private HMA macdHma;
		private double balance = 10000;
    	private double balance_update;

		protected override void OnStateChange() {

			// Compute
			if (State == State.SetDefaults) {

				// Compute
				Description = Custom.Resource.NinjaScriptStrategyDescriptionSampleMACrossOver;

				// Name
				Name = "CRON";
				
				// Set
				Fast = 15;
				Slow = 350;
				Slope = 15;
				HourStart = 4;
				HourEnd = 20;
				GapLimit = 0;
			    RsiPeriod = 14;
			    RsiLimit = 22;
				
				// Set
				IsInstantiatedOnEachOptimizationIteration = false;

			} else if (State == State.DataLoaded) {

				// Ma
				ma1 = EMA(Fast);
				ma2 = HMA(Slow);
				slope = HMA(Slope);
				rsi = RSI(Close, RsiPeriod, 1);
				macdIndicator = MACD(Close, (int)(12 * MacdFactor), (int)(26 * MacdFactor), (int)(9 * MacdFactor));
				macdHma = HMA(macdIndicator.Default, 5);
				
				// Balance
				balance_update = balance;

				// Plot
				ma1.Plots[0].Brush = Brushes.Goldenrod;
				ma2.Plots[0].Brush = Brushes.SeaGreen;
				slope.Plots[0].Brush = Brushes.CadetBlue;

				// Chart
				AddChartIndicator(ma1);
				AddChartIndicator(ma2);
				AddChartIndicator(slope);
				AddChartIndicator(rsi);
				AddChartIndicator(macdHma);
			}
		}

		protected override void OnBarUpdate() {
		
		    if (CurrentBar < BarsRequiredToTrade) return;
		
		    // Cross
		    bool crosslong  = CrossAbove(ma1, ma2, 1);
		    bool crossshort = CrossBelow(ma1, ma2, 1);
		
		    // Pending
		    if (crosslong) {
		        pendinglong = true;
		        pendingshort = false;
		    } else if (crossshort) {
		        pendingshort = true;
		        pendinglong = false;
		    }
		
		    // Se non c’è pending, non serve calcolare nulla
		    if (!pendinglong && !pendingshort) return;
		
		    // Slope
		    bool slopelong = true, slopeshort = true;
		    if (Slope > 0) {
		        slopelong  = slope[0] - slope[1] >= 0;
		        slopeshort = slope[0] - slope[1] <= 0;
		    }
		
		    // Gap
		    bool gaplong = true, gapshort = true;
			if (GapLimit > 0) {
			    gaplong  = ((Open[0] - Close[1]) / Close[1] * 100) <= GapLimit;
			    gapshort = ((Open[0] - Close[1]) / Close[1] * 100) >= -GapLimit;
			}
			
			// Rsi
			bool rsilong  = rsi[0] < (50 + RsiLimit);
			bool rsishort = rsi[0] > (50 - RsiLimit);

			// Macd
			bool macdlong  = macdHma[0] >= 0;
			bool macdshort = macdHma[0] <= 0;
		
		    // Quantity
		    int quantity = Math.Max(1, (int)Math.Floor(balance_update / Close[0]));
		    if (quantity < 1) return;
		
		    // Position
		    if (pendinglong && slopelong && gaplong && rsilong && macdlong && Position.MarketPosition != MarketPosition.Long) {
		        EnterLong(quantity);
		        pendinglong = false;
		    } else if (pendingshort && slopeshort && gapshort && rsishort && macdshort && Position.MarketPosition != MarketPosition.Short) {
		        EnterShort(quantity);
		        pendingshort = false;
		    }
		}

		#region Properties

		// Fast
		[Range(5, 500), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "MA 1", GroupName = "NinjaScriptStrategyParameters", Order = 0)]
		public int Fast { get; set; }

		// Slow
		[Range(5, 1000), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "MA 2", GroupName = "NinjaScriptStrategyParameters", Order = 1)]
		public int Slow { get; set; }

		// Slope
		[Range(2, 100), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "Slope", GroupName = "NinjaScriptStrategyParameters", Order = 2)]
		public int Slope { get; set; }

		// Hour start
		[Range(4, 12), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "Hour start", GroupName = "NinjaScriptStrategyParameters", Order = 3)]
		public int HourStart { get; set; }

		// Hour end
		[Range(16, 24), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "Hour end", GroupName = "NinjaScriptStrategyParameters", Order = 4)]
		public int HourEnd { get; set; }

		// Gap
		[Range(0, 10), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "Gap limit", GroupName = "NinjaScriptStrategyParameters", Order = 5)]
		public double GapLimit { get; set; }
				
		// RSI period
		[Range(12, 56), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "RSI period", GroupName = "NinjaScriptStrategyParameters", Order = 6)]
		public int RsiPeriod { get; set; }
		
		// RSI limit
		[Range(0, 40), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "RSI limit", GroupName = "NinjaScriptStrategyParameters", Order = 7)]
		public int RsiLimit { get; set; }

		// Macd factor
		[Range(1, 5), NinjaScriptProperty]
		[Display(ResourceType = typeof(Custom.Resource), Name = "Macd factor", GroupName = "NinjaScriptStrategyParameters", Order = 8)]
		public int MacdFactor { get; set; }

		#endregion
	}
}