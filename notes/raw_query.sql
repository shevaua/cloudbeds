-- affected intervals
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
