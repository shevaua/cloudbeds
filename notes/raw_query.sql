-------------------------------
-- affected intervals for apply
-------------------------------
select `interval`.* 
from `interval`
where
    -- left join
    (end = ($start - 1) and price = $price)
    -- right join
    or (start = ($end + 1) and price = $price)
    -- cross left or in
    or (start >= $start and start <= $end)
    -- crowss right or in
    or (end >= $start and end <= $end)
    -- cover
    or (start < $start and end > $end)

--------------------------------
-- affected intervals for delete
--------------------------------
select `interval`.* 
from `interval`
where
    -- cross left or in
    (start >= $start and start <= $end)
    -- crowss right or in
    or (end >= $start and end <= $end)
    -- cover
    or (start < $start and end > $end)
    