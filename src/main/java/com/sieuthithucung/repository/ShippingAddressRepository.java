package com.sieuthithucung.repository;

import com.sieuthithucung.entity.ShippingAddressEntity;
import org.springframework.data.jpa.repository.JpaRepository;

public interface ShippingAddressRepository extends JpaRepository<ShippingAddressEntity, Long> {
}

