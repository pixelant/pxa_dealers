INSERT INTO static_countries (cn_iso_2, cn_iso_3, cn_parent_territory_uid, cn_parent_tr_iso_nr, cn_official_name_local, cn_official_name_en, cn_capital, cn_currency_uid, cn_currency_iso_3, cn_currency_iso_nr, cn_phone, cn_eu_member, cn_uno_member, cn_address_format, cn_zone_flag, cn_short_local, cn_short_en, cn_country_zones)
  SELECT 'KS', 'RKS', '10', '39', 'Republic of Kosovo', 'Republic of Kosovo', 'Pristina', '49', 'EUR', '978', '383', '0', '0', '1', '0', 'Kosovo', 'Kosovo', '0' FROM DUAL
WHERE NOT EXISTS
  (SELECT cn_iso_3 FROM static_countries WHERE cn_iso_3='RKS');