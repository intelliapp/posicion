select name as departamento 
from "public"."Geoms" 
where ST_Contains(gmtr, ST_SetSRID(%s,4326))
and tipo = 1