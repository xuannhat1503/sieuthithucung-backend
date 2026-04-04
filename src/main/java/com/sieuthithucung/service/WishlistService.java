package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.WishlistDto;
import com.sieuthithucung.entity.WishlistEntity;
import com.sieuthithucung.mapper.WishlistMapper;
import com.sieuthithucung.repository.WishlistRepository;
import org.springframework.stereotype.Service;

@Service
public class WishlistService extends AbstractCrudService<WishlistEntity, Long, WishlistDto> {

    public WishlistService(WishlistRepository repository, WishlistMapper mapper) {
        super(repository, mapper, "Wishlist");
    }
}

