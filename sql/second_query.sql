select p1.place_id , p1.osm_id, p1.name as nombrevia,
p2.type as TipoParent , p2.name as NombreParent,
p3.type as TipoParent2, p3.isin
from placex p1
left join placex p2
on p1.parent_place_id = p2.place_id
left join placex p3
on p2.parent_place_id = p3.place_id
WHERE ST_DWithin(ST_SetSRID(%s,4326), p1.geometry, 0.001)
AND (p1.name is not null or p1.housenumber is not null)
AND p1.class = 'highway'
AND (ST_GeometryType(p1.geometry) not in ('ST_Polygon','ST_MultiPolygon')
OR ST_DWithin(ST_SetSRID(%s,4326), ST_Centroid(p1.geometry), 0.001))
ORDER BY ST_distance(ST_SetSRID(%s,4326), p1.geometry) ASC