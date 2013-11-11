# Symfony
## 1.4.20-vend-0.1

Based on the latest stable version of Symfony 1.4 (at the time of writing:
1.4.20), with BC-breaking (but important) fixes applied.

### Changelog

* Applied Countable to `Doctrine_Null`. `count(new Doctrine_Null())` will now
  return 0.
* Applied a fix for doctrine/doctrine1#8, also known as DC-797: optional 1-to-1
  relations were causing faulty state after hydration.
* Introduced a change to Doctrine_Connection in createQuery to pass the invoking 
  connection to the static query constructor so the expected connection is used.
* Introduced a change to Doctrine_Table in createQuery to pass the connection set 
  on the table to the static query constructor so the expected connection is used.
