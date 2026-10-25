# Vendored runtime compatibility sources

php-casbin is the unmodified runtime source of casbin/casbin v3.25.1 except two explicit nullable parameter annotations. Its Apache-2.0 license and original package metadata are retained in php-casbin/.

IPTools is s1lentium/iptools v1.2.0, with two (integer) casts changed to (int). Its MIT license and original package metadata are retained in iptools/.

Public namespaces, matching algorithms, adapter interfaces and policy evaluation behavior are retained. Composer replace entries prevent duplicate copies of these namespaces. No Casbin 4 API migration is performed.
