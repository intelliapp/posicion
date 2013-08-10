select name as ciudad
from "Geoms"
where "tipo"=3 
and ST_Contains(gmtr, ST_SetSRID(%s,4326))