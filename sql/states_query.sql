select name as departamento 
from "Geoms" 
where ST_Contains(gmtr, ST_SetSRID(%s,4326))
and tipo = 1