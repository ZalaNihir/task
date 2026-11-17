
















- Stock Movemnts Table 
- Created for the few reasons I have to maintain the history for the stocks what happened to the stocks so thats why


4.4
- I used database-level aggregation because the report needs total stock and reserved quantities across multiple warehouses. Using SUM keeps the calculation in the database instead of loading all stock records into PHP. COALESCE ensures products without stock records still return zero instead of null.

- Whenever a stock adjustment is successfully completed, I invalidate the inventory summary cache. I do this after the database transaction commits, so failed stock adjustments don't unnecessarily clear the cache. The next summary request fetches fresh data and caches it again.
