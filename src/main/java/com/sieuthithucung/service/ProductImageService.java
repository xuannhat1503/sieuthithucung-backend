package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.ProductImageDto;
import com.sieuthithucung.entity.ProductImageEntity;
import com.sieuthithucung.mapper.ProductImageMapper;
import com.sieuthithucung.repository.ProductImageRepository;
import org.springframework.stereotype.Service;

@Service
public class ProductImageService extends AbstractCrudService<ProductImageEntity, Long, ProductImageDto> {

    public ProductImageService(ProductImageRepository repository, ProductImageMapper mapper) {
        super(repository, mapper, "Product image");
    }
}

