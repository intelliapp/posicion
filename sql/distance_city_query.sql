select name as ciudad , ST_Distance_Sphere(ST_SetSRID(%s,4326), ST_Geometry(gmtr))/1000 as distancia
from "public"."Geoms" 
where ST_DWithin(ST_SetSRID(%s,4326), ST_Geometry(gmtr), 1)
and tipo = 2
ORDER BY ST_distance(ST_SetSRID(%s,4326), ST_Geometry(gmtr)) ASC LIMIT 1
