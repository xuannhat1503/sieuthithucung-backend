package com.sieuthithucung.repository;

import com.sieuthithucung.entity.WishlistEntity;
import org.springframework.data.jpa.repository.JpaRepository;

public interface WishlistRepository extends JpaRepository<WishlistEntity, Long> {
}

