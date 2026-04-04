package com.sieuthithucung.service;

import com.sieuthithucung.common.AbstractCrudService;
import com.sieuthithucung.dto.ShippingAddressDto;
import com.sieuthithucung.entity.ShippingAddressEntity;
import com.sieuthithucung.mapper.ShippingAddressMapper;
import com.sieuthithucung.repository.ShippingAddressRepository;
import org.springframework.stereotype.Service;

@Service
public class ShippingAddressService extends AbstractCrudService<ShippingAddressEntity, Long, ShippingAddressDto> {

    public ShippingAddressService(ShippingAddressRepository repository, ShippingAddressMapper mapper) {
        super(repository, mapper, "Shipping address");
    }
}

