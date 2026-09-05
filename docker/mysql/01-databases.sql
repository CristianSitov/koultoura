-- SetYearlyDatabase middleware opens a connection per year from the first URL
-- segment, so all three must exist. Empty is fine for / and /2026; /2024 and
-- /2022 need real content data.
CREATE DATABASE IF NOT EXISTS wcm_2022;
CREATE DATABASE IF NOT EXISTS wcm_2024;
CREATE DATABASE IF NOT EXISTS wcm_2026;
