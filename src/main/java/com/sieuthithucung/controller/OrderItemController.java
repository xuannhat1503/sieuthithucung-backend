package com.sieuthithucung.controller;

import com.sieuthithucung.common.AbstractCrudController;
import com.sieuthithucung.dto.OrderItemDto;
import com.sieuthithucung.service.OrderItemService;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/api/v1/order-items")
public class OrderItemController extends AbstractCrudController<Long, OrderItemDto> {

    public OrderItemController(OrderItemService service) {
        super(service);
    }
}

