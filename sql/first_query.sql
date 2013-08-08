Select count(p1.place_id) from placex p1
WHERE ST_DWithin(ST_SetSRID(%s,4326), p1.geometry, 0.001)
AND (p1.name is not null or p1.housenumber is not null)
AND p1.class = 'highway'
AND (ST_GeometryType(p1.geometry) not in ('ST_Polygon','ST_MultiPolygon'))