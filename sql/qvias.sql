SELECT "Descr" as Via , "KM"  
FROM "public"."Vertices"  
INNER JOIN "public"."Vias" 
ON "Vias"."ID" = "Vertices"."Via"
WHERE "Long" > %s - 0.005
  AND "Long" < %s + 0.005
  AND "Lat"  >  %s - 0.005
  AND "Lat"  <  %s + 0.005
ORDER BY sqrt(Power(%s-"Long",2)+ Power(%s-"Lat",2)) LIMIT 1