package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.ProductImageDto;
import com.sieuthithucung.service.ProductImageService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/product-images")
public class ProductImageController extends AbstractCrudController<Long, ProductImageDto> {

    public ProductImageController(ProductImageService service) {
        super(service);
    }
}

