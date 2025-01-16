customer
    cid, cname, address, ...


rewards

rid, amount, timestamp , cid




top 2 users with highest rewards in jan


select * from rewards INNER JOIN cust ON customer.id = rewards.cid ;

select cid, sum(amount) from rewards GROUP BY cid ORDER ;