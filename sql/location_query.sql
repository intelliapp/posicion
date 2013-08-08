select p1.place_id , p1.osm_id, p1.class, p1.type, p1.name, p1.parent_place_id, 
ST_distance(ST_SetSRID(%s,4326), p1.geometry) * 1000 as Distancia
from placex p1
WHERE (p1.name is not null or p1.housenumber is not null)
and ST_DWithin((Select p2.geometry from placex p2 where place_id=%s), p1.geometry, 0.02) 
ORDER BY ST_distance(ST_SetSRID(%s,4326), p1.geometry) ASC