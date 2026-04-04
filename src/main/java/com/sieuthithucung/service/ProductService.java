package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.ProductDto;
import com.sieuthithucung.entity.ProductEntity;
import com.sieuthithucung.mapper.ProductMapper;
import com.sieuthithucung.repository.ProductRepository;
import org.springframework.stereotype.Service;

@Service
public class ProductService extends AbstractCrudService<ProductEntity, Long, ProductDto> {

    public ProductService(ProductRepository repository, ProductMapper mapper) {
        super(repository, mapper, "Product");
    }
}

