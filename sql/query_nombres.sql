select p1.place_id , p1.osm_id, p1.name as nombrevia, p2.type as TipoParent , p2.name as NombreParent, p3.type as TipoParent2, p3.name as NombreParent2, p3.isin 
from placex p1 left join placex p2 on p1.parent_place_id = p2.place_id left join placex p3 on p2.parent_place_id = p3.place_id 
WHERE p1.place_id = %s