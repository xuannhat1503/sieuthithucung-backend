package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.ProductDto;
import com.sieuthithucung.service.ProductService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/products")
public class ProductController extends AbstractCrudController<Long, ProductDto> {

    public ProductController(ProductService service) {
        super(service);
    }
}

